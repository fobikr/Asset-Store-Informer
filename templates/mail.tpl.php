<?php
    $styles = array(
        "tbl" => "display: table; margin-bottom: 20px; width: 100%;",
        "tbl-header" => "display: table-row; background-color: #EEEEEE;",
        "tbl-row" => "display: table-row;",
        "tbl-cell" => "display: table-cell;",
        "cell-type" => "display: table-cell; width: 100px;",
        "cell-time" => "display: table-cell; width: 200px",
        "cell-asset" => "display: table-cell; width: 250px;",
        "cell-info" => "display: table-cell; text-align: left; min-width: 300px; padding-right: 20px;",
        "bold" => "font-weight: bold;",
        "even" => "background-color: #EEEEEE;"
    );
?>
<div style="text-align: center; display: block; min-width: 800px; width: 100%;">
    <div style="background-color: #9DCE2C; height: 48px;">
        <div style="margin-left: 20px;" align="left">
            <a style="text-decoration: none;" href="<?php print asi_host . asi_root; ?>">
                <div style="display: inline-block; vertical-align: middle;"><img src="<?php print asi_host . asi_root; ?>images/logo.png"></div>
                <div style="font-size: 28px; font-weight: bold; color: #F5F5F5; display: inline-block; font-family: Garamond,serif; vertical-align: middle;">Asset Store Informer</div>
            </a>
        </div>
    </div>
    <div style="background-color: #709221; height: 12px;"></div>
    <div style="<?php print $styles['tbl']; ?>">
        <div style="<?php print $styles['tbl-header']; ?>">
            <div style="<?php print $styles['cell-type']; ?>">Type</div>
            <div style="<?php print $styles['cell-time']; ?>">Time UTC</div>
            <div style="<?php print $styles['cell-asset']; ?>">Asset</div>
            <div style="<?php print $styles['cell-info']; ?>"></div>
        </div>
        <?php 
            $odd = TRUE;
            foreach (Informer::$records as $record)
            {
                $type = $record['type'];
                $params = $record['params'];
        ?>
            <div style="<?php print $styles['tbl-row']; if (!$odd) print $styles['even']; ?>">
                <div style="<?php print $styles['cell-type']; ?>"><?php print ucfirst($type); ?></div>
                <div style="<?php print $styles['cell-time']; ?>"><?php print gmdate("Y-m-d H:i:s", $record['time']); ?></div>
                <div style="<?php print $styles['cell-asset']; ?>"><?php print $record['asset']; ?></div>
                <div style="<?php print $styles['cell-info']; ?>">
                    <?php
                        if ($type === 'sale')
                        {
                            print $params['offset'] . " x " . $params['price'] . " = " . $params['offset'] * $params['price'] . "$";
                        }
                        elseif ($type === 'charge' || $type === 'refunding')
                        {
                            print "-" . $params['offset'] . " x " . $params['price'] . " = -" . $params['offset'] * $params['price'] . "$";
                        }
                        elseif ($type === 'rating')
                        {
                            print "Now asset rating: " . $params['newrating'];
                        }
                        elseif ($type === 'review')
                        {
                            print "<div style='$styles[bold]'>$params[subject]</div>";
                            print "<div>by <a href='https://www.assetstore.unity3d.com/#/user/$params[user_id]' target='_blank'>$params[user_name]</a></div>";
                            if ($params['rating']) print "<div><strong>Rating: </strong> $params[rating]</div>";
                            print "<div>" . str_replace("\n", "<br/>", $params['full']) . "</div>";
                        }
                        elseif ($type === "event")
                        {
                            print $params['message'];
                        }
                    ?>
                </div>
            </div>
        <?php 
                $odd = !$odd; 
            }
        ?>
    </div>
    <div style="<?php print $styles['tbl']; ?>">
        <?php 
            $monthID = gmdate("Ym");
            $month = DB::tableg('months')->where('asset_id', 0)->where('month', $monthID)->first();
            $publisher = DB::tableg('publisher')->first();
        ?>
        <div style="<?php print $styles['tbl-header']; ?>">
            <div style="<?php print $styles['tbl-cell']; ?>"></div>
            <div style="<?php print $styles['tbl-cell']; ?>">Sales</div>
            <div style="<?php print $styles['tbl-cell']; ?>">Refundings</div>
            <div style="<?php print $styles['tbl-cell']; ?>">Charges</div>
            <div style="<?php print $styles['tbl-cell']; ?>">Gross</div>
            <div style="<?php print $styles['tbl-cell']; ?>">Net</div>
        </div>
        <div style="<?php print $styles['tbl-row']; ?>">
            <div style="<?php print $styles['tbl-cell']; ?>"><?php print "Total " . gmdate("F Y"); ?></div>
            <div style="<?php print $styles['tbl-cell']; ?>"><?php print $month['sales']; ?></div>
            <div style="<?php print $styles['tbl-cell']; ?>"><?php print $month['refundings']; ?></div>
            <div style="<?php print $styles['tbl-cell']; ?>"><?php print $month['charges']; ?></div>
            <div style="<?php print $styles['tbl-cell']; ?>"><?php print $month['total'] . "$"; ?></div>
            <div style="<?php print $styles['tbl-cell']; ?>"><?php print $month['total'] * $publisher['payout_cut'] . "$"; ?></div>
        </div>
    </div>
    <div style="background-color: #709221; height: 24px; border-top: 3px solid #9DCE2C; margin-top: 20px;"></div>
    <div style="background-color: #496012; height: 10px;"></div>
</div>