<?php

class Informer
{
    public static $hasCharges = FALSE;
    public static $hasEvents = FALSE;
    public static $hasRatings = FALSE;
    public static $hasRecords = FALSE;
    public static $hasRefundings = FALSE;
    public static $hasReviews = FALSE;
    public static $hasSales = FALSE;
    
    public static $lastSendTime = 0;
    public static $period = 0;
    
    public static $records = array();

    public static function Add($type, $time, $asset, $params)
    {
        self::$records[] = array(
            "type" => $type,
            "time" => $time,
            "asset" => $asset,
            "params" => $params
        );
        
        self::UpdateMarkers($type);
        
        if (self::$period > 0)
        {
            DB::tableg('informer')->insert(array(
                "type" => $type,
                "time" => $time,
                "asset" => $asset,
                "params" => json_encode($params)
            ));
        }
    }
    
    public static function Clear()
    {
        DB::truncateTableg('informer');
    }
    
    public static function Load()
    {
        $records = DB::tableg('informer')->get()->fetchAll();
        
        foreach ($records as $record)
        {
            $type = $record['type'];
            
            self::$records[] = array(
                "type" => $type,
                "time" => $record['time'],
                "asset" => $record['asset'],
                "params" => json_decode($record['params'])
            );
            
            self::UpdateMarkers($type);
        }
    }
    
    public static function Send()
    {
        if (self::$hasRecords) self::SendRecords ();
        
        $t = time();
        if (self::$period > 0) $t -= $t % self::$period;
        DB::tableg('publisher')->update(array(
            "lastSendTime" => gmdate('Y-m-d H:i:s', $t)
        ));
        
        self::Clear();
    }
    
    private static function SendRecords()
    {
        $fn = ASI_FOLDER . "/templates/mail.tpl.php";
        
        if (!file_exists($fn))
        {
            Log::p("Mail template not exists");
            return;
        }
        
        ob_start();
        require_once($fn);
        $html_content = ob_get_contents();
        ob_end_clean();
        
        $html_content = "<html><head></head><body>$html_content</body></html>";
        
        $text_content = "";
        
        foreach (self::$records as $record)
        {
            $type = $record['type'];
            $asset = $record['asset'];
            $params = $record['params'];
            if ($type === 'sale') $text_content .= "$asset ($ " . $params['price'] . ") +" . $params['offset'];
            elseif ($type === 'charge' || $type === 'refunding') $text_content .= "$asset ($ " . $params['price'] . ") -" . $params['offset'];
            elseif ($type === 'rating') $text_content .= "$asset rating";
            elseif ($type === 'review') $text_content .= "$asset review";
            $text_content .= ";\n";
        }
        
        $subjects = array();
        
        if (self::$hasSales) $subjects[] = "sales";
        if (self::$hasCharges) $subjects[] = "charges";
        if (self::$hasRefundings) $subjects[] = "refundings";
        if (self::$hasRatings) $subjects[] = "ratings";
        if (self::$hasReviews) $subjects[] = "reviews";
        if (self::$hasEvents) $subjects[] = "events";
        
        $subject = "New " . implode(" and ", $subjects);
        
        $mime_boundary = 'Multipart_Boundary_x'.md5(time()).'x';

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\r\n";
        $headers .= "From: " . $GLOBALS['settings']['mail_from'] . "\r\n";
        $headers .= 'Date: '.date('n/d/Y g:i A')."\r\n";
        
        $body= "--$mime_boundary\r\n";
        $body.= "Content-Type: text/plain; charset=utf-8\r\n";
        $body.= "Content-Transfer-Encoding: binary\r\n";
        $body.= "\r\n";
        $body.= "\r\n";
        $body.= $text_content;
        $body.= "\r\n";
        $body.= "\r\n";

        $body.= "--$mime_boundary\r\n";
        $body.= "Content-Type: text/html; charset=utf-8\r\n";
        $body.= "Content-Transfer-Encoding: binary\r\n";
        $body.= "\r\n";
        $body.= $html_content;
        $body.= "\r\n";
        $body.= "--$mime_boundary--\n";
        
        mail($GLOBALS['settings']['mail_to'], $subject, $body, $headers); 
    }
    
    private static function UpdateMarkers($type)
    {
        self::$hasRecords = TRUE;
        
        if ($type === "sale") self::$hasSales = TRUE;
        elseif ($type === "charge") self::$hasCharges = TRUE;
        elseif ($type === "refunding") self::$hasRefundings = TRUE;
        elseif ($type === "rating") self::$hasRatings = TRUE;
        elseif ($type === "review") self::$hasReviews = TRUE;
        elseif ($type === "event") self::$hasEvents = TRUE;
    }
}
