<?php

function cpy($source, $dest)
{
    if(is_dir($source)) 
    {
        $dir_handle=opendir($source);
        while($file=readdir($dir_handle))
        {
            if($file != "." && $file != "..")
            {
                if(is_dir($source . "/" . $file))
                {
                    if(!is_dir($dest . "/" . $file)) mkdir($dest . "/" . $file);
                    cpy($source . "/" . $file, $dest . "/" . $file);
                } 
                else copy($source . "/" . $file, $dest . "/" . $file);
            }
        }
        closedir($dir_handle);
    } 
    else copy($source, $dest);
}

function CURL($url, $params = array())
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
    $content = curl_exec($ch);

    curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errorNumber = curl_errno($ch);

    if ($errorNumber != '22')
    {
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return substr( $content, $header_size );
    }
    Log::f("CURL failed: $url", "curl.log");
    curl_close($ch);
    return FALSE;
}

function generateAssetPath($assetTitle)
{
    $path = $assetTitle;
    $path = trim($path, " \t\n\r\0\x0B");
    $path = preg_replace('/[^a-zA-Z0-9\-]/', '-', $path);
    $path = strtolower($path);
    $path = preg_replace('/\-+/', '-', $path);
    $path = trim($path, "-");
    return $path;
}

function rrmdir($dir) 
{
    if (is_dir($dir)) 
    {
        $objects = scandir($dir);
        foreach ($objects as $object) 
        {
            if ($object != "." && $object != "..") 
            {
                if (filetype($dir . "/" . $object) == "dir") rrmdir($dir . "/".$object); 
                else unlink ($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

function strcode($str, $passw="")
{
   $salt = "Dn8*#2n!9j";
   $len = strlen($str);
   $gamma = '';
   $n = $len>100 ? 8 : 2;
   while( strlen($gamma)<$len )
   {
      $gamma .= substr(pack('H*', sha1($passw.$gamma.$salt)), 0, $n);
   }
   return $str^$gamma;
}

function strencode($str, $pass="")
{
    return base64_encode(strcode(($str), $pass));
}

function strdecode($str, $pass="")
{
    return strcode(base64_decode($str), $pass);
}

function validateValue($value, $regex = "/[^a-zA-Z0-9\.\-]/")
{
    return preg_match($regex, $value) == 0;
}

function zip_extract($file, $extractPath) 
{
    $zip = new ZipArchive;
    $res = $zip->open($file);
    
    if ($res === TRUE) 
    {
        $zip->extractTo($extractPath);
        $zip->close();
        return TRUE;
    } 
    else return FALSE;
}