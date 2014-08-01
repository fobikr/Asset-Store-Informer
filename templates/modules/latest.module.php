<div class="latest">
    <?php 
        print LoadJS('latest', 'module'); 
        print LoadCSS('latest', 'module');
    ?>
    <div class="latest-header">
        <div class='latest-type'>Type</div>
        <div class='latest-date'>Time UTC</div>
        <?php if ($GLOBALS['category'] != 'assets'): ?>
            <div class='latest-asset'>Asset</div>
        <?php endif; ?>
        <div class='latest-info'></div>
    </div>
</div>