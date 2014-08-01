<div class="button nextButton" onclick="removeInstallFolder();">Finish</div>
<div>
    <h2>Installation is complete.</h2>
    <div>
        <div id="finish-step">
            <div class="redText bold" >Last step:</div>
            <div>Add a new job in cron on your server (run every minute):</div>
            <div class="bold" id="finish-cron">
                <div><?php print "* * * * *     wget -O /dev/null -q http://" . $_SERVER["HTTP_HOST"] . "/" . asi_root . "update.php" ?></div>
                <div>or</div>
                <div>* * * * *     /usr/bin/php ~/PATH_TO_ASSET_STORE_INFORMER/update.php > /dev/null</div>
                <div>or</div>
                <div>* * * * *     php -f /PATH_TO_ASSET_STORE_INFORMER/update.php</div>
            </div>
            <div>For detailed instructions on setting up cron, you can find in the documentation your hosting provider.</div>
        </div>
        <div id="finish-thanks">Many thanks for the installation of our product.</div>
        <div>If you have any questions, please email us: <a href="mailto:support@infinity-code.com">support@infinity-code.com</a></div>
        <div>Enjoy your use.</div>
    </div>
</div>
<script>
    function removeInstallFolder()
    {
        INSTALL.ajax("remove-install", {}, function (){
            location.reload();
        }, "json");
    }
</script>