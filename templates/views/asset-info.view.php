<?php print GetContent("asset-info", "module"); ?>
<div class="asset-content">
    <script>
        ASI.asset_id = "<?php print $GLOBALS['asset_id']; ?>";
        ASI.isAsset = true;

        ASI.payout_cut = <?php print $GLOBALS['payout_cut']; ?>;
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
    <?php endwhile;
        }
    ?>
    </script>
    <?php
        print GetContent('date-range-selector', 'module');
        print GetContent('sales-chart', 'module');
        print GetContent('sales-info', 'module');
        print GetContent('latest-switcher', 'module');
        print GetContent('latest', 'module');
    ?>
</div>