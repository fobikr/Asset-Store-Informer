<?php
    $assets = DB::tableg('assets')->orderBy('title')->get();
    if (!empty($assets)):
?>
<?php print LoadCSS("all-assets", "view"); ?>
<div class="all-assets">
    <?php while ($row = $assets->fetchArray()): ?>
        <?php 
            $keyimage = $row['keyimage_small'];
            if (empty($keyimage)) $keyimage = rootFolder() . "images/no-image.png";
        ?>
        <div class="asset">
            <div class="asset-image"><a href="<?php print rootFolder() . "assets/" . $row['path']; ?>"><img src="<?php print $keyimage; ?>"/></a></div>
            <div class="asset-info">
                <div class="asset-title"><h3><a href="<?php print rootFolder() . "assets/" . $row['path']; ?>"><?php print $row['title']; ?></a></h3></div>
                <div>
                    <div class="inline bold">Category: </div>
                    <div class="inline asset-category"><a href="<?php print $row['category_short_url']; ?>" target="_blank"><?php print $row['category_label']; ?></a></div>
                </div>
                <?php if ($row['rating_count'] > 0): ?>
                    <div class="asset-rating">
                        <div class="inline bold">Rating:</div>
                        <div class="rating-star inline" score="<?php print $row['rating_average']; ?>"></div>
                        <?php if (!empty($row['rating_count'])): ?>
                            <div class="inline">(<?php print $row['rating_count']; ?> total)</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div>Not enough ratings</div>
                <?php endif; ?>
                <div class="asset-reviews">
                    <div class="inline bold">Reviews: </div>
                    <div class="inline">
                        <a href="<?php print rootFolder() . "assets/" . $row['path'] . "/reviews"; ?>">
                            <?php print DB::tableg('reviews')->where("asset_id", $row["id"])->count(); ?> (View all)
                        </a>
                    </div>
                </div>
                <div class="asset-description"><?php print $row['description']; ?></div>
            </div>
        </div>
        <hr class="faded" />
    <?php endwhile; ?>
</div>

<script>
    ASI.ApplyRaty();
</script>
<?php endif; ?>