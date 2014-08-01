<?php

class Asset
{
    public $id;
    public $title;
    public $description;
    public $version;
    public $version_id;
    public $min_unity_version;
    public $size;
    public $pubdate;
    public $price;
    public $short_url;
    public $category_label;
    public $category_short;
    public $keyimage_small;
    public $keyimage_icon;
    public $keyimage_big;
    public $rating_average = 0;
    public $rating_count = 0;
    public $hotness;

    public $charge_count = 0;
    public $net;
    public $gross;
    public $refunding_count = 0;
    public $sell_count = 0;

    private function checkChanges($type, $count, $modifier)
    {
        $tableID = $type . "s";
        $val = FALSE;
        $offset = 0;
        $update = TRUE;
        $t = gmdate("Y-m-01 00:00:00", time());
        $res = DB::tableg($tableID)->where('asset_id', $this->id)->where('time', '>', $t)->orderBy('time', 'DESC')->first();
        if (!empty($res))
        {
            if ($count > $res['count']) $offset = $count - $res['count'];
            else $update = FALSE;
        }
        else $offset = $count;
        
        if ($offset > 0) $val = $offset;
        
        if ($update) 
        {
            $data = array(
                "asset_id" => $this->id, 
                "count" => $count, 
                "offset" => $offset,
                "price" => $this->price,
                "time" => gmdate("Y-m-d H:i:s", time()),
            );
            
            DB::tableg($tableID)->insert($data);
            
            Informer::Add($type, time(), $this->title, array(
                "price" => $this->price,
                "offset" => $offset
            ));
            
            $monthID = gmdate("Ym", time());
            
            $curMonth = DB::tableg('months')->where("asset_id", $this->id)->where("month", $monthID)->first();
            
            if (empty($curMonth))
            {
                $curMonth = $this->getMonthRecord($this->id, $monthID);
                $curMonth[$tableID] = $offset;
                $curMonth["total"] = $offset * $modifier * $this->price;
                DB::tableg('months')->insert($curMonth);
            }
            else 
            {
                $data = array(
                    "$tableID" => $curMonth[$tableID] + $offset,
                    "total" => $curMonth["total"] + $offset * $modifier * $this->price
                );
                DB::tableg('months')->where("asset_id", $this->id)->where("month", $monthID)->update($data);
            }
            
            $totalMonth = DB::tableg('months')->where("asset_id", 0)->where("month", $monthID)->first();
            
            if (empty($totalMonth))
            {
                $totalMonth = $this->getMonthRecord(0, $monthID);
                $totalMonth[$tableID] = $offset;
                $totalMonth["total"] = $offset * $modifier * $this->price;
                DB::tableg('months')->insert($totalMonth);
            }
            else 
            {
                $data = array(
                    "$tableID" => $totalMonth[$tableID] + $offset,
                    "total" => $totalMonth["total"] + $offset * $modifier * $this->price
                );
                DB::tableg('months')->where("asset_id", 0)->where("month", $monthID)->update($data);
            }
        }
        
        return $val;
    }
    
    public function checkCharges($count) 
    {
        $this->checkChanges('charge', $count, -1);
    }

    public function checkRefundings($count) 
    {
        $this->checkChanges('refunding', $count, -1);
    }

    public function checkSales($count) 
    {
        $this->checkChanges('sale', $count, 1);
    }
    
    private function getMonthRecord($assetID, $monthID)
    {
        return array(
            "asset_id" => $assetID,
            "month" => $monthID,
            "sales" => 0,
            "charges" => 0,
            "refundings" => 0,
            "total" => 0
        );
    }

    private function insertNewRecord()
    {
        $result = AssetStore::getAssetFromResult($this->title);
        if (!result) return FALSE;
        
        $rating = $result->{'rating'};
        
        $this->id = $result->{'id'};
        $this->rating_average = $rating->{'average'};
        $this->rating_count = $rating->{'count'};
        
        $path = generateAssetPath($this->title);
        
        $data = array(
            "title" => $this->title, 
            "price" => $this->price, 
            "short_url" => $this->short_url,
            "id" => $result->{'id'}, 
            "pubdate" => $result->{'pubdate_iso'},
            "rating_average" => $rating->{'average'},
            "rating_count" => $rating->{'count'},
            "hotness" => $result->{'hotness'},
            "path" => $path,
        );
        DB::tableg('assets')->insert($data);
        TaskManager::addTask(TaskManager::UPDATE_ASSET, $this->id);
        TaskManager::addTask(TaskManager::UPDATE_REVIEWS, $this->id);
        return TRUE;
    }

    public function loadFromAAData($aaData, $short_url)
    {
        $this->title = $aaData[0];
        $this->price = substr($aaData[1], 2, strlen($aaData[1]) - 5);
        $this->sell_count = $aaData[2];
        $this->refunding_count = $aaData[3];
        $this->charge_count = $aaData[4];
        $this->gross = substr($aaData[5], 2, strlen($aaData[5]) - 5);
        $this->net = $this->gross * 0.7;
        $this->short_url = $short_url;
    }

    public function loadInfo()
    {
        foreach (AssetStore::$assets as $asset)
        {
            if ($asset->title == $this->title) 
            {
                $this->id = $asset->id;
                return TRUE;
            }
        }
        return $this->insertNewRecord();
    }
    
    public function loadLocal($data)
    {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->price = $data['price'];
        $this->size = $data['size'];
        $this->description = $data['description'];
        $this->short_url = $data['short_url'];
        $this->version_id = $data['version_id'];
        $this->rating_average = $data['rating_average'];
        $this->rating_count = $data['rating_count'];
        $this->category_label = $data['category_label'];
        $this->category_short = $data['category_short_url'];
        $this->publishnotes = $data['publishnotes'];
        $this->min_unity_version = $data['min_unity_version'];
        $this->pubdate = $data['pubdate'];
        $this->keyimage_small = $data['keyimage_small'];
        $this->keyimage_icon = $data['keyimage_icon'];
        $this->keyimage_big = $data['keyimage_big'];
    }
    
    public function updateInfoFromResult($res)
    {
        $this->id = $res->{'id'};
        $this->pubdate = $res->{'pubdate_iso'};
        
        $data = array(
            'id' => $this->id, 
            "pubdate" => $this->pubdate
        );
        
        DB::tableg('assets')->where('id', $this->id)->update($data);
    }
}