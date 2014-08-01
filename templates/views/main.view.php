<?php
    $uri = trim($_SERVER['REQUEST_URI'], "/ \t\n\r\0\x0B");
    
    $rootStrLen = strlen(asi_root);
    $uri = substr($uri, ($rootStrLen > 1)? $rootStrLen: 0);
    
    $search_start = strpos($uri, '?');
    if ($search_start !== FALSE) $uri = substr ($uri, 0, $search_start);
    
    $GLOBALS['uri'] = $uri = explode("/", $uri);
    $categoryList = array(
        "assets",
        "publisher",
        "reviews",
        "settings",
        "update",
    );
    
    if (empty($uri[0]) || $uri[0] === "index.php") $GLOBALS['category'] = 'publisher';
    else if (in_array($uri[0], $categoryList)) $GLOBALS['category'] = $uri[0];
    else $GLOBALS['category'] = "notfound";
    
    $GLOBALS['updates'] = DB::tableg('updates')->get()->fetchAll();
    
    print LoadJS('combobox', 'base');
    print LoadCSS('combobox', 'base');
    print LoadCSS("main", "view");
    
    print GetContent('menu', 'module');
    print GetContent('verify-invoice', 'module');
    if (count($GLOBALS['updates']) > 0) print GetContent('new-updates', 'module');
?>
<div class="view-content">
    <?php
        if ($GLOBALS['category'] == "publisher") 
        {
            if (empty($uri[1])) print GetContent ('publisher-info', 'view');
            else PageNotFound ();
        }
        elseif ($GLOBALS['category'] == "assets") 
        {
            $asset_path = !empty($uri[1])? strtolower($uri[1]): "";
            if (!$asset_path) print GetContent('assets', 'view');
            else if (validateValue($asset_path) && DB::tableg('assets')->where("path", $asset_path)->count())
            {
                if (empty($uri[2])) print GetContent('asset-info', 'view');
                else if ($uri[2] === 'reviews') print GetContent('reviews', 'view');
                else PageNotFound ();
            }
            else PageNotFound (); 
        }
        elseif ($GLOBALS['category'] == 'reviews')
        {
            if (empty($uri[1])) print GetContent('reviews', 'view');
            else PageNotFound ();
        }
        elseif ($GLOBALS['category'] == "settings")
        {
            if (empty($uri[1])) print GetContent("settings", "view");
            else
            {
                if ($uri[1] == "change-password") print GetContent("change-password", "view");
                else PageNotFound();
            }
        }
        elseif ($GLOBALS['category'] == "update")
        {
            if (empty($uri[1])) print GetContent('update', 'view');
            else PageNotFound ();
        }
        else PageNotFound();
    ?>
</div>
<div id="view-footer">
    <div>
        <div>Documentation: <a target="_blank" href="http://infinity-code.com/docs/asset-store-informer">http://infinity-code.com/docs/asset-store-informer</a></div>
        <div>Support: <a target="_blank" href="mailto:support@infinity-code.com">support@infinity-code.com</a></div>
    </div>
    <div><a href="http://infinity-code.com" target="_blank">Infinity Code 2014</a></div>
</div>
<div id="view-post-footer"></div>