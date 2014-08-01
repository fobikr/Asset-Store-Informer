<?php

session_start();

header('Content-type: text/html; charset=utf-8');

define('ASI_FOLDER', getcwd());

function rootFolder()
{
    return "/" . asi_root;
}

if (!file_exists("log")) mkdir ("log");
ini_set("log_errors", 1);
ini_set("error_log", "log/php-error.log");

date_default_timezone_set('UTC');

require_once 'php/log.php';

if (file_exists('php/settings.php'))
{
    require_once 'php/config.php';

    $_SESSION['auth-key'] = secret_login;

    require_once 'php/db.php';

    if (!DB::connect(asi_db_server, asi_db_username, asi_db_password, asi_db_name)) 
    {
        print "Failed. Can not connect to MySQL.";
        exit();
    }
    
    require_once 'templates/page-index.tpl.php';
}
else require_once 'install/templates/install.tpl.php';