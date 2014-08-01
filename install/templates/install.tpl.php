<?php
    $rootPath = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"]);
    $rootPath = substr($rootPath, 0, strrpos($rootPath, "/"));
    $rootPath = trim($rootPath, "\\/");

    if ($rootPath != '') $rootPath .= "/";
    
    define("asi_root", $rootPath);

    require_once 'php/contentmanager.php';

    if (empty($_SESSION['asi_install'])) $_SESSION['asi_install'] = array();
    if (empty($_SESSION['asi_install']['key'])) $_SESSION['asi_install']['key'] = hash("md5", uniqid(mt_rand(), true));
    if (empty($_SESSION['asi_install']['salt1'])) $_SESSION['asi_install']['salt1'] = hash("md5", uniqid(mt_rand(), true));
    if (empty($_SESSION['asi_install']['salt2'])) $_SESSION['asi_install']['salt2'] = hash("md5", uniqid(mt_rand(), true));
    if (empty($_SESSION['asi_install']['open_key'])) $_SESSION['asi_install']['open_key'] = hash("md5", uniqid(mt_rand(), false));
?>

<html>
    <head>
        <title>Asset Store Informer - Installer</title>
        <?php 
            print LoadJS("jquery", "base");
            print LoadJS("jquery-ui", "base");
            print LoadJS("json2", "base");
            print LoadJS("install/install", "root");
            print LoadCSS("base", "base");
            print LoadCSS("install/install", "root");
            print LoadCSS("jquery-ui", "base", FALSE);
            print GetLink("icon", "images/favicon.ico", "image/x-icon");
            print GetLink("shortcut icon", "images/favicon.png", "image/png");
            print GetLink("icon", "images/favicon.png", "image/png");
        ?>
        
        <script>
            INSTALL.key = "<?php print $_SESSION['asi_install']['key']; ?>";
            INSTALL.rootPath = "<?php print "/" . $rootPath; ?>";
        </script>
    </head>
    <body>
        <div class="content-wrapper install-window div-center shadow" onkeypress="return installOnKeyPress(event)">
            <?php
                require_once 'install/templates/welcome.tpl.php';
            ?>
        </div>
    </body>
</html>