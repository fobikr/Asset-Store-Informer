<?php require_once ASI_FOLDER . '/php/contentmanager.php'; ?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Asset Store Informer</title>
        <?php 
            print LoadJS("jquery", 'base');
            print LoadJS("jquery-ui", 'base');
            print LoadJS("jquery.raty.min", 'base');
            print LoadJS("date.format", 'base');
            print LoadJS("asi", 'base');
            print LoadCSS("jquery-ui", 'base', FALSE);
            print LoadCSS("base", "base");
            print GetLink("shortcut icon", "images/favicon.ico", "image/x-icon");
            print GetLink("icon", "images/favicon.ico", "image/x-icon");
            print GetLink("shortcut icon", "images/favicon.png", "image/png");
            print GetLink("icon", "images/favicon.png", "image/png");
        ?>
    </head>
    <body>
        <?php if (!empty($_SESSION['session_key'])): ?>
            <div class="temp" style="display: none">
                 <script> 
                     ASI.key = "<?php print $_SESSION['session_key']; ?>";
                     ASI.rootPath = "<?php print rootFolder(); ?>";
                     $(".temp").remove();
                 </script>
            </div>
        <?php endif; ?>
        <div class="content-wrapper">
            <?php 
                if (isset($_SESSION["success"]) && $_SESSION["success"] === TRUE ) print GetContent('main', 'view');
                else print GetContent('login', 'view');
            ?>
        </div>
    </body>
</html>