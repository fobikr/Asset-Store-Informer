<?php
    $asset_id = DB::tableg('assets')->where($GLOBALS['uri'][1], 'path')->select("id");
    $sales = DB::tableg('sales')->findAll('asset_id', $asset);
    
    if (!empty($sales)):
?>
<div>
    <?php while ($row = $sales->fetchArray()): ?>
    
    <?php endwhile; ?>
</div>
<?php endif; ?>
