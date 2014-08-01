<?php print LoadCSS('latest-switcher', 'module'); ?>
<hr class="faded"/>
<div class="latest-switcher">
    <div class="latest-switcher-title">Type filter:</div>
    <div class="switch">
        <input id="latest-switcher-sales" class="cmn-toggle cmn-toggle-yes-no" type="checkbox" checked>
        <label for="latest-switcher-sales" data-on="Sales" data-off="Sales"></label>
    </div>
    <div class="switch">
        <input id="latest-switcher-charges" class="cmn-toggle cmn-toggle-yes-no" type="checkbox" checked>
        <label for="latest-switcher-charges" data-on="Charges" data-off="Charges"></label>
    </div>
    <div class="switch">
        <input id="latest-switcher-refundings" class="cmn-toggle cmn-toggle-yes-no" type="checkbox" checked>
        <label for="latest-switcher-refundings" data-on="Refundings" data-off="Refundings"></label>
    </div>
    <div class="switch">
        <input id="latest-switcher-ratings" class="cmn-toggle cmn-toggle-yes-no" type="checkbox" checked>
        <label for="latest-switcher-ratings" data-on="Ratings" data-off="Ratings"></label>
    </div>
    <div class="switch">
        <input id="latest-switcher-reviews" class="cmn-toggle cmn-toggle-yes-no" type="checkbox" checked>
        <label for="latest-switcher-reviews" data-on="Reviews" data-off="Reviews"></label>
    </div>
    <div class="switch">
        <input id="latest-switcher-events" class="cmn-toggle cmn-toggle-yes-no" type="checkbox" checked>
        <label for="latest-switcher-events" data-on="Events" data-off="Events"></label>
    </div>
</div>
<?php print LoadJS("latest-switcher", 'module'); ?>