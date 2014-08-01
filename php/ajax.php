<?php

if (!defined('ASI_FOLDER'))
{
    chdir('../');
    define('ASI_FOLDER', getcwd());
}

if (!file_exists("log")) mkdir ("log");
ini_set("log_errors", 1);
ini_set("error_log", "log/php-error.log");

require_once 'log.php';
require_once 'utils.php';

if (!isset($_POST) || empty($_POST['action']) || empty($_POST['key'])) Log::e ("Bad request");

session_start();
date_default_timezone_set('UTC');

if (empty($_SESSION['session_key']) || $_POST['key'] != $_SESSION['session_key']) 
{
    Log::e ("Bad request");
}

function DBConnect()
{
    require_once 'config.php';
    require_once 'db.php';

    return DB::connect(asi_db_server, asi_db_username, asi_db_password, asi_db_name);
}

function status($value = "failed")
{
    print '{"status": "' . $value . '"}';
    exit();
}

function success()
{
    status("success");
}

$action = $_POST['action'];
if (!validateValue($action)) exit();

function ActionGetContent()
{
    if (empty($_POST['name']) || empty($_POST['type'])) Log::e ("Bad request");

    if (!DBConnect()) exit();
    
    require_once 'contentmanager.php';
    print GetContent($_POST['name'], $_POST['type']);
}

function ActionGetData()
{
    if (!isset($_POST['from']) || !isset($_POST['to'])) Log::e ("Bad request");
    if (!DBConnect()) exit();
    
    require_once 'contentmanager.php';
    $asset_id = !empty($_POST['asset_id'])?$_POST['asset_id']: '';
    $type = !empty($_POST['contentType'])?$_POST['contentType']: 'ALL';
    print GetData($_POST['from'], $_POST['to'], $asset_id, $type);
}

function ActionVerifyInvoice()
{
    if (empty($_POST['invoice']) || !is_numeric($_POST['invoice'])) Log::e ("Bad request");

    if (!DBConnect()) status();
    
    $result = DB::tableg('publisher')->select(array('name', 'invoice_key'))->first();
    $settings = DB::tableg('settings')->first();
    $key = strdecode($result['invoice_key'], salt . $settings['salt']);

    $url = "http://api.assetstore.unity3d.com/publisher/v1/invoice/verify.json?key=$key&invoice=" . $_POST['invoice'];
    if (($response = CURL($url)) === FALSE) status ();
    
    print $response;
}

function ActionUpdateInvoice()
{
    if (!DBConnect()) status ();
    
    $publisher = DB::tableg('publisher')->select(array('id'))->first();
    $settings = DB::tableg('settings')->first();
    $id = $publisher["id"];
    $session = strdecode(uas_session, salt . $settings['salt']);

    $url = "https://publisher.assetstore.unity3d.com/api/publisher-info/api-key/$id.json";
    if (($response = CURL($url, array(
        "header" => array("X-Unity-Session: $session"),
        "postfields" => "user=xxx@xxx.com&pass=xxx&skip_terms=true"))) === FALSE) status ();
    
    $json = json_decode($response);
    DB::tableg('publisher')->update(array(
        "invoice_key" => $json->{"api_key"}
    ));
    success();
}

function ActionSaveSettings()
{
    if (!DBConnect()) exit();
    
    DB::tableg('settings')->update(array(
        "mail_from" => $_POST['mail-from'],
        "mail_to" => $_POST['mail-to'],
        "informer_period" => $_POST['freq']
    ));
}

function ActionChangePassword()
{
    if (!DBConnect()) status();
    
    $login = DB::escape($_POST['login']);
    $pass = hash("sha256", $_POST['oldpass'] . $_SESSION['auth_key']);

    $result = DB::tableg('users')->select("access")->where("login", $login)->where("pass", $pass)->first();

    if (empty($result)) status();

    $newPass = hash("sha256", $_POST['newpass'] . $_SESSION['auth_key']);
    DB::tableg('users')->where("login", $login)->update(array("pass" => $newPass));
    success();
}

function ActionLogout()
{
    unset($_SESSION['access']);
    unset($_SESSION['success']);
    unset($_SESSION['session_key']);
}

function ActionStartUpdate()
{
    if (!DBConnect()) status();
    $updates = DB::tableg('updates')->get()->fetchAll();
    if (empty($updates)) status("No updates");
    
    DB::tableg('settings')->update(array("install_update", 1));
    
    $versions = array();
    mkdir('updates');
    
    foreach ($updates as $update)
    {
        $fn = "updates/" . $update['filename'];
        $versions[] = $update['version'];
        
        if (file_exists($fn)) continue;
        
        $response = CURL("http://infinity-code.com/products_update/asi/get-update.php", array(
            "postfields" => "version=" . $update['version'] . "&code=" . md5("asi_" . $update['version'])
        ));
        
        if (!$response) status ();
        
        file_put_contents($fn, $response);
    }
    
    sort($versions, SORT_NUMERIC);
    
    $result = array(
        "status" => "success",
        "versions" => $versions
    );
    
    print json_encode($result);
    exit();
}

function ActionApplyUpdate()
{
    if (empty($_POST['id']) || !is_numeric($_POST['id'])) status();
    $id = $_POST['id'];
    
    if (!DBConnect()) status();
    
    $temp = 'updates/temp';
    
    if (file_exists($temp)) rrmdir ($temp);
    mkdir($temp);
    
    if (!zip_extract("updates/$id.update", $temp)) status();
    
    if (!file_exists($temp . "/update.php")) status();
    require_once $temp . "/update.php";
    
    if (file_exists($temp . "/files")) cpy($temp . "/files", "./");
    
    if (function_exists("ASIUpdateFS")) ASIUpdateFS();
    if (function_exists("ASIUpdateDatabase")) ASIUpdateDatabase();
    
    DB::tableg('updates')->where("version", $id)->delete();
    
    rrmdir ($temp);
    unlink("updates/$id.update");
    
    success();
}

function ActionFinishUpdate()
{
    $temp = 'updates/temp';
    if (file_exists($temp)) rrmdir ($temp);
    rmdir('updates');
    
    if (!DBConnect()) status();
    DB::tableg('settings')->update(array("install_update", 0));
    success();
}

if ($action === 'getcontent') ActionGetContent();
elseif ($action === 'getdata') ActionGetData();
elseif ($action === 'verify-invoice') ActionVerifyInvoice();
elseif ($action === 'update-invoice') ActionUpdateInvoice();
elseif ($action === 'save-settings') ActionSaveSettings();
elseif ($action === 'change-password') ActionChangePassword();
elseif ($action === 'logout') ActionLogout();
elseif ($action === 'start-update') ActionStartUpdate();
elseif ($action === 'apply-update') ActionApplyUpdate();
elseif ($action === 'finish-update') ActionFinishUpdate();