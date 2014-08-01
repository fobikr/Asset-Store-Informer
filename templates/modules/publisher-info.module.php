<?php
    $publisher = DB::tableg('publisher')->first();
    $title = $publisher['name'];
    $description = $publisher['description'];
    $keyimage_small = $publisher['keyimage_small'];
    if (empty($keyimage_small)) $keyimage_small = rootFolder() . "images/no-image.png";
    $rating_average = $publisher['rating_average'];
    $rating_count = $publisher['rating_count'];
    $first_sale = strtotime($publisher['first_sale']);
    $GLOBALS['first_year'] = gmdate("Y", $first_sale);
    $GLOBALS['payout_cut'] = $publisher['payout_cut'];
    
    print LoadCSS('publisher-info', 'view');
?>
<div id="publisher" class="info-block">
    <div class="logo"><img src="<?php print $keyimage_small; ?>"/></div>
    <div class="publisher-details">
        <div><h2><?php print $title; ?></h2></div>
        <div class="button" id="openInAssetStore"><a href="<?php print $publisher["short_url"]; ?>" target="_blank">Open in Unity Asset Store</a></div>
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
                <a href="<?php print rootFolder(); ?>reviews">
                    <?php print DB::tableg('reviews')->count(); ?> (View all)
                </a>
            </div>
        </div>
        <hr class="faded"/>
        <div class="fulldescription vscroll"><?php print $description; ?></div>
        <hr class="faded"/>
    </div>
</div>