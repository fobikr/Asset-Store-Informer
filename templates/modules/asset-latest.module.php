<?php 
    $asset_res = DB::tableg('assets')->select(array('title', 'id', 'path'))->where('path', $GLOBALS['uri'][1])->get();
    if (!empty($asset_res))
    {
        $assets_info = array();
        while ($row = $asset_res->fetchArray()) 
        {
            $assets_info[$row['id']] = array(
                'title' => $row['title'],
                'path' => $row['path']
            );
        }
    }
?>
<?php if (isset($assets_info) && $GLOBALS['sales-info']): ?>
<div class="latest">
    <?php print LoadCSS("latest", "module"); ?>
    <div class="latest-header">
        <div class='latest-type'>Type</div>
        <div class='latest-date'>Time</div>
        <div class='latest-title'>Title</div>
        <div class='latest-price'>Price $</div>
        <div class='latest-count'>Count</div>
        <div class='latest-gross'>Gross $</div>
    </div>
    <?php while ($row = $GLOBALS['sales-info']->fetchArray()): ?>
    <div class="latest-row latest-row-<?php print $row['atype'];?>">
        <div><?php print ucfirst($row['atype']);?></div>
        <div><?php print $row['time'];?></div>
        <div>
            <?php 
                $asset_id = $row['asset_id'];
                if ($asset_id != 0) print "<a href='" . rootFolder() . "assets/" . $assets_info[$asset_id]['path'] . "'>" . $assets_info[$asset_id]['title'] . "</a>";
                else print "Unknown";
            ?>
        </div>
        <div></div>
        <div></div>
        <div></div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>