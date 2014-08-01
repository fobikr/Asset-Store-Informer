<?php 
    $settings = DB::tableg('settings')->first();
    $period = $settings["informer_period"];
    
    print LoadCSS("settings", "view"); 
?>

<div id="settings-wrapper">
    <h2>Settings</h2>
    <div>
        <label for="email-from">Send mail:</label>
        <div id="cbSendEmail">
            <span>Immediately</span>
            <ul class="dropdown">
                <li><a>Immediately</a></li>
                <li><a>Summary per time</a></li>
                <li><a>Never</a></li>
            </ul>
        </div>
    </div>
    <div id="blockSendFrequency" class="hidden">
        <label for="email-from">Frequency of sending mails:</label>
        <div id="cbFrequencySendEmail">
            <span>One hour</span>
            <ul class="dropdown">
                <li sec="3600"><a>One hour</a></li>
                <li sec="10800"><a>Three hours</a></li>
                <li sec="21600"><a>Six hours</a></li>
                <li sec="43200"><a>Twelve hours</a></li>
                <li sec="86400"><a>One day</a></li>
                <li sec="604800"><a>One week</a></li>
            </ul>
        </div>
    </div>
    <div id="blockSendInfo">
        <div class="install-textfield-block-2">
            <label for="email-from">Mail from:</label>
            <input type="text" maxlength="60" size="60" value="<?php print $settings['mail_from']; ?>" id="email-from" placeholder="deamon@your-domain.com">
            <label for="email-to">Mail to:</label>
            <input type="text" maxlength="60" size="60" value="<?php print $settings['mail_to']; ?>" id="email-to">
        </div>
    </div>
    <div id="btnUpdateInvoiceKey" class="button">
        <a>Update invoice API key</a>
    </div>
    <div id="btnChangePassword" class="button">
        <a>Change password</a>
    </div>
    <div id="btnSave" class="button">
        <a>Save settings</a>
    </div>
</div>

<?php print LoadJS("settings", "view"); ?>

<script>
    cbMail.setIndex(<?php 
        if ($period == 0) print 0;
        elseif ($period == -1) print 2;
        else print 1;
    ?>);
        
    <?php if ($period > 0): ?>
        cbFrequency.setValue($("#cbFrequencySendEmail li[sec='<?php print $period; ?>']").text());
    <?php endif; ?>
</script>