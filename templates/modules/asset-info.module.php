<?php
    $asset = DB::tableg('assets')->find('path', $GLOBALS['uri'][1]);
    $title = $asset['title'];
    $description = $asset['description'];
    $keyimage_small = $asset['keyimage_small'];
    if (empty($keyimage_small)) $keyimage_small = rootFolder() . "images/no-image.png";
    $rating_average = $asset['rating_average'];
    $rating_count = $asset['rating_count'];
    if (empty($rating_count)) $rating_count = 0;
    
    $first_sale = strtotime($asset['first_sale']);
    $GLOBALS['first_year'] = gmdate("Y", $first_sale);
    
    $publisher = DB::tableg('publisher')->first();
    $GLOBALS['payout_cut'] = $publisher['payout_cut'];
    
    $GLOBALS['asset_id'] = $asset['id'];
    
    print LoadCSS('asset-info', 'view');
?>
<div id="asset" class="info-block">
    <div class="logo"><img src="<?php print $keyimage_small; ?>"/></div>
    <div class="asset-details">
        <div><h2><?php print $title; ?></h2></div>
        <div class="button" id="openInAssetStore"><a href="<?php print $asset["short_url"]; ?>" target="_blank">Open in Unity Asset Store</a></div>
        <div>
            <div class="inline bold">Category: </div>
            <div class="inline asset-category"><a href="<?php print $asset['category_short_url']; ?>" target="_blank"><?php print $asset['category_label']; ?></a></div>
        </div>
        <?php if ($rating_count > 0): ?>
            <div class="details">
                <div class="inline bold">Rating:</div>
                <div class="rating-star inline" score="<?php print $rating_average; ?>"></div>
                <div class="inline">(<?php print $rating_count; ?> total)</div>
            </div>
        <?php else: ?>
            <div>Not enough ratings</div>
        <?php endif; ?>
        <div>
            <div class="inline bold">Reviews: </div>
            <div class="inline">
                <a href="<?php print rootFolder() . "assets/" . $asset['path'] . "/reviews"; ?>">
                    <?php print DB::tableg('reviews')->where("asset_id", $asset["id"])->count(); ?> (View all)
                </a>
            </div>
        </div>
        <hr class="faded"/>
        <div class="fulldescription vscroll"><?php print $description; ?></div>
        <hr class="faded"/>
    </div>
</div>