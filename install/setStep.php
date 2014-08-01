<?php

if (!isset($_POST) || empty($_POST['step'])) exit();

session_start();

$step = preg_replace ("/[^a-zA-Z0-9\s]/", "", $_POST['step']);

if ((empty($_SESSION['asi_install']) || empty($_SESSION['asi_install']['key']) || $_POST['key'] != $_SESSION['asi_install']['key']) && $step !== "finish") exit();

if (!defined('ASI_FOLDER'))
{
    chdir('../');
    define('ASI_FOLDER', getcwd());
}

if (!defined('asi_root'))
{
    $rootPath = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"]);
    $rootPath = substr($rootPath, 0, strrpos($rootPath, "/"));
    $rootPath = substr($rootPath, 0, strrpos($rootPath, "/"));
    $rootPath = trim($rootPath, "\\/");

    if ($rootPath != '') $rootPath .= "/";

    define("asi_root", $rootPath);
}

if (!file_exists("log")) mkdir ("log");
ini_set("log_errors", 1);
ini_set("error_log", "log/php-error.log");

require_once 'php/contentmanager.php';
require_once "install/templates/$step.tpl.php";