<?php 

    $isAsset = $GLOBALS['category'] == "assets";
    
    print GetContent($isAsset? "asset-info": "publisher-info", "module");
    print GetContent("latest", "module");
?>
<script>
    <?php  if (!$isAsset):?>
        ASI.assets = [];
        <?php
            $asset_res = DB::tableg('assets')->select(array('title', 'id', 'path'))->get();
            if (!empty($asset_res))
            {
                $assets_info = array();
                while ($row = $asset_res->fetchArray()):
        ?>
            ASI.assets['<?php print $row['id']; ?>'] = {
                title: "<?php print $row['title']; ?>",
                path: "<?php print $row['path']; ?>"
            };
        <?php 
                endwhile;
            }
        endif; 
    ?>
    
    ASI.addLatestRowTypeClass = false;
    ASI.asset_id = "<?php print $isAsset?$GLOBALS['asset_id']: ""; ?>";
    ASI.isAsset = ASI.asset_id != "";
    
    $(document).ready(function() {
        ASI.GetData(0, "NOW", ASI.asset_id, OnGetLatest, "reviews");
    });
</script>