<?php print LoadCSS("new-updates", "module"); ?>

<div id="dialog-updates" title="Updates available">
    <?php foreach ($GLOBALS['updates'] as $update): ?>
    <div class="update-item">
        <div class="update-version"><?php print $update['version']; ?></div>
        <div class="update-date"><?php print gmdate("Y-m-d", strtotime($update['date'])); ?></div>
        <div class="update-changelog"><?php print $update['changelog']; ?></div>
    </div>
    <?php endforeach; ?>
</div>

<script>
    $( "#dialog-updates" ).dialog({
        autoOpen: false,
        width: 350,
        modal: true,
        position: {
            my: "top+10",
            at: "top+10"
        },
        buttons: {
            "Update": function() {
                ASI.href("update");
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
</script>