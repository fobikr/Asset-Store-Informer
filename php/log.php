<?php

class Log
{
    public static function e($text)
    {
        print ($text);
        exit();
    }
    
    public static function f($text, $fn = 'asi.log')
    {
        if (!file_exists("log")) mkdir ("log");
        $fn = "log/" . $fn;
        error_log($text . "\n", 3, $fn);
    }

    public static function p($text)
    {
        print $text . "</br>";
    }
    
    public static function r($obj)
    {
        print_r($obj);
        print "<br/>";
    }
}