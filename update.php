<?php

chdir(dirname(__FILE__));
define('ASI_FOLDER', getcwd());

if (!file_exists('php/settings.php')) exit();

if (!file_exists("log")) mkdir ("log");
ini_set("log_errors", 1);
ini_set("error_log", "log/php-error.log");

require_once 'php/config.php';
require_once 'php/db.php';
require_once 'php/taskmanager.php';
require_once 'php/assetstore.php';
require_once 'php/log.php';
require_once 'php/multicurl.php';
require_once 'php/informer.php';
require_once 'php/updater.php';
require_once 'php/utils.php';
require_once 'php/version.php';

$start = microtime(TRUE);

if (!DB::connect(asi_db_server, asi_db_username, asi_db_password, asi_db_name))
{
    print "Failed. Can not connect to MySQL.";
    exit();
}

$GLOBALS['settings'] = DB::tableg('settings')->first();

if ($GLOBALS['settings']['install_update'] == 1) exit();

Informer::Load();

$GLOBALS['salt'] = salt . $GLOBALS['settings']['salt'];
Informer::$period = $GLOBALS['settings']['informer_period'];

if (!AssetStore::loadPublisherInfo()) exit();

TaskManager::start();

if (MultiCURL::$hasCurls) AssetStore::$curlTime += MultiCURL::Execute();

if (Informer::$period == 0) Informer::Send ();
elseif (Informer::$period > 0 && Informer::$lastSendTime + Informer::$period > time()) Informer::Send ();

DB::close();