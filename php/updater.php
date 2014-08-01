<?php

class Updater
{
    public static function Check()
    {
        $response = CURL("http://infinity-code.com/products_update/asi/check-update.php", array(
            "postfields" => "version=" . version . "&code=" . md5("asi_" . version)
        ));
        if ($response === FALSE) 
        {
            Log::p ("Cannot check update");
            return;
        }
        $versions = json_decode($response);
        foreach ($versions as $v)
        {
            $vs = DB::tableg('updates')->where("version", $v->{'id'})->first();
            if (empty($vs))
            {
                DB::tableg('updates')->insert(array(
                    "version" => $v->{'id'},
                    "date" => $v->{'date'},
                    "changelog" => $v->{'changelog'},
                    "filename" => $v->{'filename'}
                ));
            }
        }
    }
}