<?php

function GetContent($name, $type)
{
    if (!validateValue($name)) return "";
    
    if ($type === "view") $fn = ASI_FOLDER . "/templates/views/$name.view.php";
    else if ($type === "module") $fn = ASI_FOLDER . "/templates/modules/$name.module.php";
    else return "";
    
    if (!file_exists($fn)) 
    {
        Log::f("Cannot find $fn", "ContentManager.log");
        return "";
    }

    ob_start();
    require_once($fn);
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

function GetData($from = 0, $to = 'NOW', $asset_id = '', $type = 'ALL')
{
    $type = strtolower($type);
    
    $from = gmdate("Y-m-d H:i:s", $from);
    $addTotalMonth = $to === "NOW" && $type == 'all';
    
    if ($type == "all") $sales_query = DB::tableg('sales')->select(array('time', 'asset_id', 'count', 'offset', 'price'))->where('time', '>', $from);
    if ($type == "all") $charges_query = DB::tableg('charges')->select(array('time', 'asset_id', 'count', 'offset', 'price'))->where('time', '>', $from);
    if ($type == "all") $refundings_query = DB::tableg('refundings')->select(array('time', 'asset_id', 'count', 'offset', 'price'))->where('time', '>', $from);
    if ($type == "all") $ratings_query = DB::tableg('ratings')->select(array('time', 'asset_id', 'count', 'offset', 'oldrating', 'newrating'))->where('time', '>', $from);
    if ($type == "all") $events_query = DB::tableg('events')->where('time', '>', $from);
    if ($type == "all" || $type == 'reviews') $reviews_query = DB::tableg('reviews')->where('date', '>', $from);
    
    if ($to !== "NOW")
    {
        $to = gmdate("Y-m-d H:i:s", $to);
        if ($type == "all") $sales_query = $sales_query->where('time', '<', $to);
        if ($type == "all") $charges_query = $charges_query->where('time', '<', $to);
        if ($type == "all") $refundings_query = $refundings_query->where('time', '<', $to);
        if ($type == "all") $ratings_query = $ratings_query->where('time', '<', $to);
        if ($type == "all") $events_query = $events_query->where('time', '<', $to);
        if ($type == "all" || $type == 'reviews') $reviews_query = $reviews_query->where('date', '<', $to);
    }
    
    if ($asset_id !== '') 
    {
        if ($type == "all") $sales_query = $sales_query->where('asset_id', $asset_id);
        if ($type == "all") $charges_query = $charges_query->where('asset_id', $asset_id);
        if ($type == "all") $refundings_query = $refundings_query->where('asset_id', $asset_id);
        if ($type == "all") $ratings_query = $ratings_query->where('asset_id', $asset_id);
        if ($type == "all") $events_query = $events_query->where('asset_id', $asset_id);
        if ($type == "all" || $type == 'reviews') $reviews_query = $reviews_query->where('asset_id', $asset_id);
    }
    
    if ($type == "all") $sales_result = $sales_query->get();
    if ($type == "all") $charges_result = $charges_query->get();
    if ($type == "all") $refundings_result = $refundings_query->get();
    if ($type == "all") $ratings_result = $ratings_query->get();
    if ($type == "all") $events_result = $events_query->get();
    if ($type == "all" || $type == 'reviews') $reviews_result = $reviews_query->get();
    
    $result = array();
    if ($type == "all") $result['sales'] = $sales_result? $sales_result->fetchAll(): array();
    if ($type == "all") $result['charges'] = $charges_result? $charges_result->fetchAll(): array();
    if ($type == "all") $result['refundings'] = $refundings_result? $refundings_result->fetchAll(): array();
    if ($type == "all") $result['ratings'] = $ratings_result? $ratings_result->fetchAll(): array();
    if ($type == "all") $result['events'] = $events_result? $events_result->fetchAll(): array();
    if ($type == "all" || $type == 'reviews') $result['reviews'] = $reviews_result? $reviews_result->fetchAll(): array();
    
    if ($addTotalMonth)
    {
        $month = DB::tableg('months')->where("asset_id", ($asset_id == '')?0: $asset_id)->orderBy("month", "DESC")->first();
        $result['month'] = array(
            "month_id" => $month['month'],
            "sales" => $month['sales'],
            "refundings" => $month['refundings'],
            "charges" => $month['charges'],
            "total" => $month['total']
        );
        
        $month_id = $month['month'];
        
        $expected = DB::tableg('months')->where("asset_id", ($asset_id == '')?0: $asset_id)->where("month", ">", $month_id - 4)->where("month", "<>", $month_id)->get()->fetchAll();
        
        if (!empty($expected))
        {
            $expectedCount = 0;
            $expectedGross = 0;

            foreach ($expected as $e)
            {
                $expectedCount += $e['sales'] - $e['refundings'] - $e['charges'];
                $expectedGross += $e['total'];
            }

            $result['expected'] = array(
                "count" => $expectedCount / count($expected),
                "gross" => $expectedGross / count($expected),
            );
        }
        else
        {
            $result['expected'] = array(
                "count" => 0,
                "gross" => 0,
            );
        }
    }

    return json_encode($result);
}

function GetLink($rel, $href, $type)
{
    return "<link rel='$rel' href='/" . asi_root . "$href' type='$type'>";
}

function LoadCSS($name, $type, $inline = TRUE)
{
    if ($type === 'base') $path = "css/$name.css";
    else if ($type === 'module' || $type === 'view') $path = "css/" . $type . "s/$name.$type.css";
    else if ($type === 'root') $path = "$name.css";
    
    if (!empty($path)) 
    {
        if ($inline === TRUE && file_exists($path)) return "<style>" . file_get_contents("./$path") . "</style>";
        
        return "<link type='text/css' rel='stylesheet' href='/" . asi_root . "$path?v=" . rand() . "' />";
    }
    return "";
}

function LoadJS($name, $type, $inline = TRUE)
{
    if ($type === 'base') $path = "js/$name.js";
    else if ($type === 'module' || $type === 'view') $path = "js/" . $type . "s/$name.$type.js";
    else if ($type === 'root') $path = "$name.js";
    
    if (!empty($path)) 
    {
        if ($inline === TRUE && file_exists($path)) return "<script>" . file_get_contents("./$path") . "</script>";
        
        return "<script src='/" . asi_root . "$path'></script>";
    }
    return "";
}

function PageNotFound()
{
    print GetContent("page-not-found", "view");
}