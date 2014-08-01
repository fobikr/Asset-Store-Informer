<?php

function exitMsg($msg = '{"result": "failed"}')
{
    print $msg;
    exit();
}

if (!isset($_POST)) exitMsg ();
if (empty($_POST['l']) || empty($_POST['p']) || empty($_POST['r'])) exitMsg();

require_once 'config.php';
require_once 'log.php';
require_once 'db.php';

if (!DB::connect(asi_db_server, asi_db_username, asi_db_password, asi_db_name)) exitMsg();

$login = DB::escape($_POST['l']);
$pass = hash("sha256", $_POST['p'] . salt);

$result = DB::tableg('users')->select("access")->where("login", $login)->where("pass", $pass)->first();
if (!empty($result))
{
    session_start();
    
    $_SESSION['access'] = $result['access'];
    $_SESSION['success'] = TRUE;
    $_SESSION['user'] = $login;
    $_SESSION['session_key'] = $key = hash("sha256", time());
    print '{"result": "success", "key": "' . $key . '"}';
}
else exitMsg();