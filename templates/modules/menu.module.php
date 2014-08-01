<div class="menu">
    <div class="menu-logo">
        <a href="<?php print rootFolder(); ?>">
            <div>
                <img src="<?php print rootFolder(); ?>images/logo.png"/>
            </div>
            <div>Asset Store Informer</div>
        </a>
    </div>
    <div class="menu-toolbar">
        <?php if (count($GLOBALS['updates']) > 0): ?>
        <div class="button icon" title="Updates available" id="menu-updates" onclick="ShowUpdates();"><img src="<?php print rootFolder(); ?>images/menu/update.png"/></div>
        <?php endif; ?>
        <div class="button icon" title="Verify invoice" id="menu-verify-invoice" onclick="ShowInvoiceForm();"><img src="<?php print rootFolder(); ?>images/menu/check_invoice.png"/></div>
        <div class="button icon" title="Settings" id="menu-settings"><img src="<?php print rootFolder(); ?>images/menu/settings.png"/></div>
        <div class="button icon" title="Logout" onclick="ASI.Logout();"><img src="<?php print rootFolder(); ?>images/menu/logout.png"/></div>
    </div>
    <div class="clearfix"></div>
    <?php print LoadCSS("menu", "module"); ?>
</div>
<div class="menu-items">
    <section>
        <ul>
            <li><a data-option-value="publisher" href="<?php print rootFolder(); ?>">Publisher</a></li>
            <li><a data-option-value="assets" href="<?php print rootFolder(); ?>assets">Assets</a></li>
        </ul>
    </section>
</div>
<script>
    $('.menu-items a[data-option-value=<?php 
        if ($GLOBALS['category'] === "reviews") print "publisher";
        else print $GLOBALS['category'];
    ?>]').addClass('selected');
</script>
<?php print LoadJS('menu', 'module'); ?>
