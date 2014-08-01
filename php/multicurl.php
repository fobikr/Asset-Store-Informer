<?php

class MultiCURL
{
    public static $hasCurls = FALSE;
    private static $tasks;
    
    public static function Add($url, $callback, $params)
    {
        if (empty(self::$tasks)) self::$tasks = array();
        
        self::$tasks[] = array(
            "ch" => self::CURL($url, $params),
            "url" => $url,
            "params" => $params,
            "callback" => $callback
        );
        
        self::$hasCurls = TRUE;
    }
    
    public static function AddFromAssetStore($url, $callback, $params)
    {
        self::Add($GLOBALS['settings']['assetstore_server'] . $url, $callback, $params);
    }
    
    public static function AddFromPublisher($url, $callback, $params)
    {
        self::Add($GLOBALS['settings']['assetstore_publisher_server'] . $url, $callback, $params);
    }
    
    public static function Clear()
    {
        unset(self::$tasks);
        self::$hasCurls = false;
    }
    
    private static function CURL($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);

        if (isset($params['header'])) curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']);

        if (isset($params['postfields'])) 
        {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['postfields']);
        }
        elseif (isset($params['post']))
        {
            curl_setopt($ch, CURLOPT_POST, $params['post']);
        }

        if (isset($params['customrequest'])) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $params['customrequest']); 
        if (isset($params['cookie'])) curl_setopt($ch, CURLOPT_COOKIE, $params['cookies']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        
        return $ch;
    }
    
    public static function Execute()
    {
        if (empty(self::$tasks)) return;
        
        $start = microtime(TRUE);
        
        $mh = curl_multi_init();

        foreach (self::$tasks as $task) 
        {
            curl_multi_add_handle($mh, $task['ch']);
        }
        
        $running = null;

        do 
        {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } 
        while ($running > 0);

        foreach (self::$tasks as $task)
        {
            $ch = $task['ch'];
            curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errorNumber = curl_errno($ch);

            if ($errorNumber != '22')
            {
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $content = substr(curl_multi_getcontent ($ch), $header_size );
            }
            else 
            {
                Log::f("CURL failed: " . $task['url'], "curl.log");
                $content = FALSE;
            }
            
            if (!empty($task['callback'])) call_user_func($task['callback'], $content);
            curl_multi_remove_handle($mh, $ch);
        }
        
        curl_multi_close($mh);
        
        return microtime(TRUE) - $start;
    }
}