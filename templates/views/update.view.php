<style>
    .updates-title {
        text-align: center;
    }
    
    .ui-progressbar {
        margin-left: 10px;
        margin-right: 10px;
        position: relative;
    }
    
    .progress-label {
        position: absolute;
        left: 50%;
        top: 4px;
        font-weight: bold;
        text-shadow: 1px 1px 0 #fff;
    }

    .redText {
        color: red;
    }

    .greenText {
        color: green;
    }

    #progress-msg {
        margin-left: 20px;
        margin-top: 10px;
    }
</style>

<div class="install-updates">
<?php if (count($GLOBALS['updates']) > 0): ?>
    <h1 class="updates-title">Updates are installed. Please wait.</h1>
    <div>
        <div id="progressbar"><div class="progress-label">Loading...</div></div>
    </div>
    <div id="progress-msg"></div>
<?php else: ?>
    <h1 class="updates-title">Updates not found.</h1>
<?php endif; ?>
</div>

<script>
    $( "#progressbar" ).progressbar({
        value: false
    });
    $( "#progressbar" ).progressbar( "option", "value", false );
    $( "#progressbar .ui-progressbar-value" ).css({
        "background": '#9DCE2C'
    });
    
    function setMessage(msg, isError)
    {
        if (isError == null) isError = false;

        var field = $("#progress-msg");

        field.text(msg);

        if (isError) field.addClass("redText");
        else field.removeClass("redText");
    }

    function progress(val)
    {
        ASI.progress = val;
        $("#progressbar").progressbar( "option", "value", val );
        $(".progress-label").text(Math.round(val) + " %");
    }
    
    function OnInstallError()
    {
        progress (100);
        setMessage("When you install update an error occurred. Try the installation again later or contact support.");
        ASI.ajax("finish-update");
    }
    
    function OnStartComplete(result)
    {
        if (result.status === "success")
        {
            ASI.update_versions = result.versions;
            ASI.update_step = 90.0 / result.versions.length;
            ASI.update_index = 0;
            progress (10);
            OnStartNextUpdate();
        }
        else OnInstallError();
    }
    
    function OnStartNextUpdate()
    {
        var id = ASI.update_versions[ASI.update_index];
        setMessage("Install version: " + id);
        ASI.ajax("apply-update", {id: id}, OnStartNextUpdateComplete, "json");
    }
    
    function OnStartNextUpdateComplete(result)
    {
        if (result.status === "success")
        {
            progress (ASI.progress + ASI.update_step);
            ASI.update_index++;
            if (ASI.update_index < ASI.update_versions.length) OnStartNextUpdate();
            else 
            {
                progress (100);
                setMessage("Install complete.");
                ASI.ajax("finish-update");
            }
        }
        else OnInstallError();
    }
    
    ASI.ajax("start-update", {}, OnStartComplete, "json");
</script>