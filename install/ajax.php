<?php

if (!defined('ASI_FOLDER'))
{
    chdir('../');
    define('ASI_FOLDER', getcwd());
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

function table($tablename)
{
    return DB::table($_SESSION['asi_install']["mysql"]['prefix'] . $tablename);
}

function DBConnect()
{
    $settings = $_SESSION['asi_install']['mysql'];
    if (!DB::connect($settings['server'], $settings['login'], $settings["password"], $settings["name"])) status ();
}

if (!isset($_POST) || empty($_POST['action']) || empty($_POST['key'])) status ("Bad request");

session_start();
date_default_timezone_set('UTC');

if (!file_exists("log")) mkdir ("log");
ini_set("log_errors", 1);
ini_set("error_log", "log/php-error.log");

if (empty($_SESSION['asi_install']) || empty($_SESSION['asi_install']['key']) || $_POST['key'] != $_SESSION['asi_install']['key'])  status ("Bad request");

require_once 'php/utils.php';

$action = $_POST['action'];
if (!validateValue($action)) status ("Bad request");

require_once 'php/db.php';
require_once 'php/log.php';

define ("asset_server", "https://www.assetstore.unity3d.com/");
define ("publisher_server", "https://publisher.assetstore.unity3d.com/");

$rootPath = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"]);
$rootPath = substr($rootPath, 0, strrpos($rootPath, "/"));
$rootPath = substr($rootPath, 0, strrpos($rootPath, "/"));
$rootPath = trim($rootPath, "\\/");

if ($rootPath != '') $rootPath .= "/";

define("asi_root", $rootPath);

function ActionCheckUASInfo()
{
    if (empty($_POST['login']) || empty($_POST['pass'])) status ("Bad request");
    
    $pub = CURL(publisher_server . "resources/javascripts/publisher.js");
    
    if ($pub === FALSE) status("Cannot get publisher.js");
    
    if (!preg_match("/var token = '(\w*)'/i", $pub, $matches)) status("Cannot get token");
    $token = $matches[1];
    
    $_SESSION['asi_install']["token"] = $token;
    
    $xsession = CURL(publisher_server . "login", array(
        "header" => array('X-Unity-Session: ' . $token . $token . $token),
        "postfields" => "user=" . $_POST["login"] . "&pass=" . $_POST["pass"] . "&skip_terms=true"
    ));
    
    if ($xsession === FALSE) status("Cannot get xsession");

    $_SESSION['asi_install']['uas_login'] = $_POST['login'];
    $_SESSION['asi_install']['uas_pass'] = $_POST['pass'];
    $_SESSION['asi_install']['xsession'] = $xsession . $token . $token;
    
    success();
}

function ActionCheckDBInfo()
{
    if (empty($_POST['server']) || empty($_POST['name']) || empty($_POST['login'])) status ("Bad request");
    if ($_POST['prefix'] != "" && !validateValue($_POST['prefix'], "/[^a-zA-Z0-9\-_]/")) status ();
    
    try
    {
        if (!DB::connect($_POST['server'], $_POST['login'], $_POST['pass'], $_POST['name'])) status();
    } catch (Exception $ex) {
        status();
    }
    
    $_SESSION['asi_install']['mysql'] = array(
        "server" => $_POST['server'],
        "login" => $_POST['login'],
        "password" => $_POST['pass'],
        "name" => $_POST['name'],
        "prefix" => $_POST['prefix'],
    );
    success();
}

function ActionSetLogin()
{
    if (empty($_POST['login']) || empty($_POST['pass'])) status ();
    
    $_SESSION['asi_install']['asi_login'] = $_POST['login'];
    $_SESSION['asi_install']['asi_pass'] = hash("sha256", $_POST['pass'] . $_SESSION['asi_install']['salt1']);
    
    success();
}

function ActionSetInformer()
{
    if (!isset($_POST["freq"])) status ("bad request");
    
    $_SESSION['asi_install']['freq'] = $_POST['freq'];
    $_SESSION['asi_install']['mail-from'] = $_POST['mail-from'];
    $_SESSION['asi_install']['mail-to'] = $_POST['mail-to'];
    success();
}

function ActionCreateTables()
{
    require_once 'install/steps/createTables.php';
    
    if (CreateTables() === FALSE) status();    
    success();
}

function ActionCreatePublisherInfo()
{
    $response = CURL(publisher_server . "api/publisher/overview.json", array(
        "header" => array('X-Unity-Session: ' . $_SESSION['asi_install']['xsession']),
        "postfields" => "user=xxx&pass=xxx&skip_terms=true"
    ));
    
    if ($response === FALSE) status ();
    
    $obj = json_decode($response);
    $overview = $obj->{'overview'};
    $id = $overview->{'id'};
    $name = $overview->{'name'};
    $description = $overview->{'description'};
    $short_url = $overview->{'short_url'};
    
    if (!empty($overview->{'rating'}))
    {
        $rating = $overview->{'rating'};
        if (!empty($rating->{'count'})) $rating_count = $rating->{'count'};
        else $rating_count = 0;
        
        if (!empty($rating->{'average'})) $rating_average = $rating->{'average'};
        else $rating_average = 0;
    }
    else
    {
        $rating_count = 0;
        $rating_average = 0;
    }
    
    $url = $overview->{'url'};
    $support_url = $overview->{'support_url'};
    $support_email = $overview->{'support_email'};
    $payout_cut = $overview->{'payout_cut'};
    
    if (!empty($overview->{'keyimage'}))
    {
        $keyimage = $overview->{'keyimage'};
        if (!empty($keyimage->{'small'})) $keyimage_small = $keyimage->{'small'};
        if (!empty($keyimage->{'big'})) $keyimage_big = $keyimage->{'big'};
    }

    $response = CURL(publisher_server . "api/publisher-info/api-key/$id.json", array(
        "header" => array('X-Unity-Session: ' . $_SESSION['asi_install']['xsession']),
        "postfields" => "user=xxx&pass=xxx&skip_terms=true"
    ));

    if ($response === FALSE) status();
    
    $obj = json_decode($response);
    $invoice_key = $obj->{"api_key"};

    DBConnect();
    $pass = $_SESSION['asi_install']['salt1'] . $_SESSION['asi_install']['salt2'];
    
    $params = array(
        "id" => $id,
        "name" => $name,
        "description" => $description,
        "short_url" => $short_url,
        "rating_count" => $rating_count,
        "rating_average" => $rating_average,
        "url" => $url,
        "support_url" => $support_url,
        "support_email" => $support_email,
        "keyimage_small" => $keyimage_small,
        "keyimage_big" => $keyimage_big,
        "payout_cut" => $payout_cut,
        "invoice_key" => strencode($invoice_key, $pass)
    );
    
    $_SESSION['asi_install']["publisher_id"] = $id;
    table("publisher")->insert($params);
    
    $response = CURL(publisher_server . "api/publisher-info/sales-periods/$id.json", array(
        "header" => array('X-Unity-Session: ' . $_SESSION['asi_install']['xsession']),
        "customrequest" => "GET"
    ));
    
    if ($response === FALSE) status();
    
    $obj = json_decode($response);
    $periods = $obj->{'periods'};

    $ps = array();

    foreach ($periods as $p)
    {
        $ps[] = $p->{'value'};
    }

    $returns = array(
        "status" => "success",
        "periods" => $ps
    );
        
    print json_encode($returns);
    exit();
}

function ActionGetAssets()
{
    $id = $_SESSION['asi_install']["publisher_id"];
    $response = CURL(publisher_server . "api/publisher/results/$id.json", array(
        "header" => array('X-Unity-Session: ' . $_SESSION['asi_install']['xsession']),
        "postfields" => "user=xxx&pass=xxx&skip_terms=true"
    ));
    
    if ($response === FALSE) status ();

    $obj = json_decode($response);
    $results = $obj->{'results'};

    $assets = array();

    foreach ($results as $r)
    {
        if (!empty($r->{'rating'}))
        {
            $rating = $r->{'rating'};
            if (!empty($rating->{'count'})) $ratingCount = $rating->{'count'};
            if (!empty($rating->{'average'})) $ratingAverage = $rating->{'average'};
        }
        
        if (!isset($ratingCount)) $ratingCount = 0;
        if (!isset($ratingAverage)) $ratingAverage = 0;

        $assets[] = array(
            "id" => $r->{'id'},
            "title" => $r->{'title'},
            "hotness" => $r->{'hotness'},
            "rating_count" => $ratingCount,
            "rating_average" => $ratingAverage
        );
    }

    $returns = array(
        "status" => "success",
        "assets" => $assets
    );

    print json_encode($returns);
    exit();
}

function ActionGetAssetInfo()
{
    if (empty($_POST['asset'])) status ("Bad request");
    
    $id = $_POST['asset'];
    $response = CURL(asset_server . "api/content/overview/$id.json", array(
        "header" => array('X-Unity-Session: ' . $_SESSION['asi_install']['xsession']),
        "customrequest" => "GET"
    ));
    
    if ($response === FALSE) status ();
    
    $obj  = json_decode($response);
    $content = $obj->{'content'};
    $title = $content->{'title'};
    $path = generateAssetPath($title);
    $rating = $content->{'rating'};
    $category = $content->{'category'};
    $keyimage = $content->{'keyimage'};
    $price = $content->{'price'};

    DBConnect();
    
    $data = array(
        "id" => $id,
        "path" => $path,
        "title" => $title,
        "min_unity_version" => $content->{'min_unity_version'},
        "size" => $content->{'size'},
        "category_label" => $category->{'label'},
        "category_short_url" => $category->{'short_url'},
        "keyimage_small" => $keyimage->{'small'},
        "keyimage_icon" => $keyimage->{'icon'},
        "keyimage_big" => $keyimage->{'big'},
        "version" => $content->{'version'},
        "version_id" => $content->{'version_id'},
        "description" => $content->{'description'},
        "title" => $content->{'title'},
        "publishnotes" => $content->{'publishnotes'},
        "short_url" => $content->{'short_url'},
        "pubdate" => $content->{'pubdate'},
        "price" => intval($price->{'USD'}),
        "rating_count" => $rating->{'count'},
        "rating_average" => $rating->{'average'},
        "hotness" => $_POST['hotness'],
    );
    table("assets")->insert($data);
    success();
}

function ActionGetSalesPeriod()
{
    if (empty($_POST['period']) && !is_numeric($_POST['period'])) status ();
    
    $period = $_POST['period'];
    $publisher = $_SESSION['asi_install']["publisher_id"];
    
    $response = CURL(publisher_server . "api/publisher-info/sales/$publisher/$period.json", array(
        "header" => array('X-Unity-Session: ' . $_SESSION['asi_install']['xsession']),
        "customrequest" => "GET"
    ));
    
    if ($response === FALSE) status();
    
    DBConnect();
    
    $obj = json_decode($response);
    $aaData = $obj->{'aaData'};
    
    $year = intval(substr($period, 0, 4));
    $month = intval(substr($period, 4, 2));

    function cmp($a, $b)
    {
        if ($a["time"] == $b["time"]) {
            return 0;
        }
        return ($a["time"] < $b["time"]) ? -1 : 1;
    }
    
    function generateArray($count, $minDate, $maxDate, $id, $price, $table)
    {
        if ($count == 0) return;
        
        $arr = array();
        
        for ($i = 0; $i < $count; $i++)
        {
            $item = array(
                "time" => mt_rand($minDate, $maxDate),
                "asset_id" => $id,
                "price" => $price
            );
            
            $arr[] = $item;
        }
        
        usort($arr, "cmp");
        
        $itemCount = 1;
        
        foreach ($arr as $item)
        {
            $data = array(
                'time' => gmdate("Y-m-d H:i:s", $item['time']),
                'asset_id' => $item['asset_id'],
                'price' => $item['price'],
                'count' => $itemCount,
                'offset' => 1
            );
            table($table)->insert($data);
            $itemCount++;
        }
    }
    
    function generateMonthItem(&$monthRecords, $id)
    {
        $monthRecords[$id] = array(
            "sales" => 0,
            "refundings" => 0,
            "charges" => 0,
            "total" => 0
        );
    }
    
    $monthRecords = array();
    generateMonthItem($monthRecords, "total");
    
    foreach ($aaData as $a)
    {
        if (empty($a[6])) continue;
        
        $asset = table('assets')->select(array(
            'id', 'first_sale'
        ))->where('title', $a[0])->first();
        $id = $asset['id'];
        $first_sale = $asset['first_sale'];
   
        $startDate = strtotime($a[6]);
        $endDate = strtotime($a[7]) + 86399;
        
        if (empty($first_sale) || $first_sale == "1970-01-01")
        {
            table('assets')->where('id', $id)->update(array('first_sale' => gmdate("Y-m-d H:i:s", $startDate)));
        }

        $price = substr($a[1], 2, strlen($a[1]) - 5);
        $salesCount = $a[2];
        $refundingsCount = $a[3];
        $chargesCount = $a[4];
        
        if (empty($salesCount)) $salesCount = 0;
        if (empty($refundingsCount)) $refundingsCount = 0;
        if (empty($chargesCount)) $chargesCount = 0;
        
        $refundingsCount = -$refundingsCount;
        $chargesCount = -$chargesCount;
        
        generateArray($salesCount, $startDate, $endDate, $id, $price, "sales");
        generateArray($refundingsCount, $startDate, $endDate, $id, $price, "refundings");
        generateArray($chargesCount, $startDate, $endDate, $id, $price, "charges");
        
        if (empty($monthRecords[$id])) generateMonthItem($monthRecords, $id);
        
        $monthRecords[$id]['sales'] += $salesCount;
        $monthRecords[$id]['refundings'] += $refundingsCount;
        $monthRecords[$id]['charges'] += $chargesCount;
        $monthRecords[$id]['total'] += ($salesCount - $refundingsCount - $chargesCount) * $price;
        
        $monthRecords["total"]['sales'] += $salesCount;
        $monthRecords["total"]['refundings'] += $refundingsCount;
        $monthRecords["total"]['charges'] += $chargesCount;
        $monthRecords["total"]['total'] += ($salesCount - $refundingsCount - $chargesCount) * $price;
    }
    
    foreach ($monthRecords as $id => $item)
    {
        $monthData = array(
            "month" => $period,
            "asset_id" => ($id != "total")? $id: 0,
            "sales" => $item['sales'],
            "refundings" => $item['refundings'],
            "charges" => $item['charges'],
            "total" => $item['total']
        );
        
        table('months')->insert($monthData);
    }

    success();
}

function ActionGetAssetComments()
{
    if (empty($_POST['asset'])) status ("Bad request");
    
    $id = $_POST['asset'];

    $response = CURL(asset_server . "api/content/comments/$id.json", array(
        "header" => array('X-Unity-Session: ' . $_SESSION['asi_install']['xsession']),
        "customrequest" => "GET"
    ));
    
    if ($response === FALSE) status ();

    $obj = json_decode($response);
    $comments = $obj->{'comments'};

    if (!$comments) success ();

    DBConnect();
    
    $asset = table('assets')->where("id", $id)->first();
    
    if (empty($asset)) status ();
    
    $rating_count = $asset['rating_count'];
    $rating_average = $asset['rating_average'];
    
    $ratings = 0;
    foreach ($comments as $comment)
    {
        $user = $comment->{'user'};
        $helpful = $comment->{'is_helpful'};
        $data = array(
            'date' => gmdate("Y-m-d H:i:s", strtotime($comment->{'date'})),
            'version' => $comment->{'version'},
            'subject' => $comment->{'subject'},
            'full' => $comment->{'full'},
            'rating' => $comment->{'rating'},
            'user_name' => $user->{'name'},
            'user_id' => $user->{'id'},
            'id' => $comment->{'id'},
            'asset_id' => $id,
            'helpful_count' => $helpful->{'count'},
            'helpful_score' => $helpful->{'score'}
        );

        if (!empty($comment->{'replies'}))
        {
            $replies = $comment->{'replies'};
            $reply = $replies[0];
            $data['reply_subject'] = $reply->{'subject'};
            $data['reply_full'] = $reply->{'full'};
            $data['reply_date'] = gmdate("Y-m-d H:i:s", strtotime($reply->{'date'}));
        }

        table('reviews')->insert($data);
        
        if (!empty($comment->{'rating'})) $ratings++;
    }
    
    if (!empty($rating_count) && $rating_count > $ratings)
    {
        $startDate = strtotime($asset['first_sale']);
        $endDate = time();
        
        $ratingsArr = array();
        
        while ($rating_count > $ratings)
        {
            $ratingsArr[] = mt_rand($startDate, $endDate);
            $ratings++;
        }
        
        sort($ratingsArr, SORT_NUMERIC);
        
        foreach ($ratingsArr as $item)
        {
            $rating_data = array(
                'time' => gmdate("Y-m-d H:i:s", $item),
                'asset_id' => $id,
                'offset' => 1,
            );

            if (!empty($rating_average)) 
            {
                $rating_data['oldrating'] = $rating_average;
                $rating_data['newrating'] = $rating_average;
            }
            table('ratings')->insert($rating_data);
        }
    }
    success();
}

function ActionFinish()
{
    DBConnect();
    
    $first_sale = table('assets')->select('first_sale')->orderBy('first_sale')->first();
    table('publisher')->update(array('first_sale' => $first_sale['first_sale']));
    
    $pass = $_SESSION['asi_install']['salt1'] . $_SESSION['asi_install']['salt2'];
    
    $asi_settings = array(
        "id" => 1,
        "mail_to" => $_SESSION['asi_install']['mail-to'],
        "mail_from" => $_SESSION['asi_install']['mail-from'],
        "informer_period" => $_SESSION['asi_install']['freq'],
        "assetstore_server" => asset_server,
        "assetstore_publisher_server" => publisher_server,
        "salt" => $_SESSION['asi_install']['salt2'],
        "token" => $_SESSION['asi_install']["token"],
    );
    
    table('settings')->insert($asi_settings);
    
    $user = array(
        "login" => $_SESSION['asi_install']['asi_login'],
        "pass" => $_SESSION['asi_install']['asi_pass'],
        "access" => "full"
    );
    
    table('users')->insert($user);
    
    function addTask($priority, $task, $asset_id)
    {
        table('tasks')->insert(array(
            "time" => gmdate("Y-m-d H:i:s", 0),
            "priority" => $priority,
            "task" => $task,
            "asset_id" => $asset_id,
            "count_failed" => 0
        ));
    }
    
    addTask(3, "UpdateSales", 0);
    addTask(6, "UpdateRating", 0);
    
    $assets = table('assets')->get()->fetchAll();
    
    foreach ($assets as $asset)
    {
        addTask(15, "UpdateAsset", $asset['id']);
        addTask(25, "UpdateReviews", $asset['id']);
    }
    
    addTask(70, "CheckUpdates", 0);
    
    success();
}

function ActionRemoveInstall()
{
    $pass = $_SESSION['asi_install']['salt1'] . $_SESSION['asi_install']['salt2'];
    
    $settings = file_get_contents("install/templates/settings.tpl.php");
    
    function replaceKey(&$str, $key, $value)
    {
        $str = str_replace("%$key%", $value, $str);
    }
    
    replaceKey($settings, "dbname", $_SESSION['asi_install']['mysql']['name']);
    replaceKey($settings, "dbpass", $_SESSION['asi_install']['mysql']['password']);
    replaceKey($settings, "dbserver", $_SESSION['asi_install']['mysql']['server']);
    replaceKey($settings, "dbusername", $_SESSION['asi_install']['mysql']['login']);
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')? "https": "http";
    
    replaceKey($settings, "asihost", "$protocol://" . $_SERVER["HTTP_HOST"] . "/");
    replaceKey($settings, "asiroot", asi_root);
    
    replaceKey($settings, "secretlogin", $_SESSION['asi_install']['open_key']);
    
    replaceKey($settings, "salt", $_SESSION['asi_install']['salt1']);
    replaceKey($settings, "tableprefix", $_SESSION['asi_install']["mysql"]['prefix']);
    replaceKey($settings, "uaslogin", strencode($_SESSION['asi_install']['uas_login'], $pass));
    replaceKey($settings, "uaspass", strencode($_SESSION['asi_install']['uas_pass'], $pass));
    replaceKey($settings, "uassession", strencode($_SESSION['asi_install']['xsession'], $pass));
    
    if (file_put_contents("php/settings.php", $settings) === FALSE) status();
    
    unset($_SESSION['asi_install']);
    unset($_SESSION['auth-key']);
    
    rrmdir("install");
    
    success();
}

if ($action === "check-uas-info") ActionCheckUASInfo();
elseif ($action === "check-db-info") ActionCheckDBInfo();
elseif ($action === "set-login") ActionSetLogin();
elseif ($action === "set-informer") ActionSetInformer();
elseif ($action === "create-tables") ActionCreateTables();
elseif ($action === "create-publisher-info") ActionCreatePublisherInfo();
elseif ($action === "get-assets") ActionGetAssets();
elseif ($action === "get-asset-info") ActionGetAssetInfo();
elseif ($action === "get-sales-period") ActionGetSalesPeriod();
elseif ($action === "get-asset-comments") ActionGetAssetComments();
elseif ($action === "finish") ActionFinish();
elseif ($action === "remove-install") ActionRemoveInstall();