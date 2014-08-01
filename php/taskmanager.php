<?php
class TaskManager
{
    const CHECK_UPDATE = "CheckUpdates";
    const UPDATE_ASSET = "UpdateAsset";
    const UPDATE_RATING = "UpdateRating";
    const UPDATE_REVIEWS = "UpdateReviews";
    const UPDATE_SALES = "UpdateSales";
    
    private static $id;
    private static $time;
    private static $priority;
    private static $task;
    private static $asset_id;
    private static $count_failed;
    
    public static function addTask($task, $asset_id = 0, $priority = NULL)
    {
        if ($priority === NULL)
        {
            if ($task == self::UPDATE_SALES) $priority = 3;
            elseif ($task == self::UPDATE_RATING) $priority = 6;
            elseif ($task == self::UPDATE_ASSET) $priority = 15;
            elseif ($task == self::UPDATE_REVIEWS) $priority = 25;
            elseif ($task == self::CHECK_UPDATE) $priority = 70;
        }
        
        $t = gmdate("Y-m-d H:i:s", 0);
        DB::tableg('tasks')->insert(array("priority"=>$priority, "task"=>$task, "asset_id"=>$asset_id, "time"=>$t));
    }
    
    public static function deleteTaskByAssetID($asset_id)
    {
        DB::tableg('tasks')->where('asset_id', $asset_id)->delete();
    }

    public static function start()
    {
        $t = gmdate("Y-m-d H:i:s");
        $tasks = DB::tableg('tasks')->where("time", "<", $t)->orderBy("priority")->orderBy("time")->get();
        if (empty($tasks)) return;
        
        $tasks = $tasks->fetchAll();
        
        $tasks_queries = array();
        
        foreach ($tasks as $curTask)
        {
            self::$id = $curTask['id'];
            self::$time = $curTask['time'];
            self::$priority = $curTask['priority'];
            $task = self::$task = $curTask['task'];
            self::$asset_id = $curTask['asset_id'];
            self::$count_failed = $curTask['count_failed'];
            
            Log::p($task);
            
            if ($task == self::UPDATE_SALES) 
            {
                AssetStore::loadAssets();
                self::updateSales();
            }
            else if ($task == self::UPDATE_RATING) 
            {
                AssetStore::loadAssets();
                self::updateRating();
            }
            else if ($task == self::UPDATE_ASSET) 
            {
                AssetStore::loadAssets();
                self::updateAsset();
            }
            else if ($task == self::UPDATE_REVIEWS) self::updateReviews();
            else if ($task == self::CHECK_UPDATE) Updater::Check();
            
            $tasks_queries[] = self::updateTime() . ";";
        }
        
        DB::multi_query(implode("\n", $tasks_queries));
        DB::clearMultiQueryResults();
    }

    private static function updateAsset()
    {
        $token = $GLOBALS['settings']['token'];
        MultiCURL::AddFromAssetStore("api/content/overview/" . self::$asset_id . ".json", "AssetStore::updateAssetInfo", array(
            "header" => array('X-Unity-Session: ' . $token . $token . $token),
            "customrequest" => "GET"
        ));
    }
    
    private static function updateRating()
    {
        $token = $GLOBALS['settings']['token'];
        MultiCURL::AddFromAssetStore("api/publisher/overview/" . AssetStore::$publisher_id . ".json", 'AssetStore::checkPublisherRating', array(
            "header" => array('X-Unity-Session: ' . $token . $token . $token),
            "customrequest" => "GET"
        ));
    }
    
    private static function updateReviews()
    {
        $token = $GLOBALS['settings']['token'];
        MultiCURL::AddFromAssetStore("api/content/comments/" . self::$asset_id . ".json", 'AssetStore::updateReviews', array(
            "header" => array('X-Unity-Session: ' . $token . $token . $token),
            "customrequest" => "GET"
        ));
    }
    
    private static function updateSales()
    {
        $assets = AssetStore::getAssetFromSales();
        if ($assets)
        {
            $usedAssets = array();
            
            foreach ($assets as $asset) 
            {
                if ($asset->loadInfo()) 
                {
                    if (empty($usedAssets[$asset->id]))
                    {
                        $usedAssets[$asset->id] = array(
                            "sales" => $asset->sell_count,
                            "charges" => abs($asset->charge_count),
                            "refundings" => abs($asset->refunding_count)
                        );
                    }
                    else
                    {
                        $curAsset = $usedAssets[$asset->id];
                        $usedAssets[$asset->id] = array(
                            "sales" => $curAsset['sales'] + $asset->sell_count,
                            "charges" => $curAsset['charges'] + abs($asset->charge_count),
                            "refundings" => $curAsset['refundings'] + abs($asset->refunding_count)
                        );
                    }
                    
                    $curAsset = $usedAssets[$asset->id];
                    
                    if ($curAsset['sales'] > 0) $asset->checkSales($curAsset['sales']);
                    if ($curAsset['charges'] > 0) $asset->checkCharges($curAsset['charges']);
                    if ($curAsset['refundings'] > 0) $asset->checkRefundings($curAsset['refundings']);
                }
            }
        }
    }
    
    private static function updateTime()
    {
        $time = time();
        $p = self::$priority;
        if ($p < 10) $time += 300;
        else if ($p < 20) $time += 600;
        else if ($p < 70) $time += 1200;
        else $time += 86400;
        $t = gmdate("Y-m-d H:i:s", $time);
        return DB::tableg('tasks')->where('id', self::$id)->updateQuery(array('time'=> $t));
    }
}