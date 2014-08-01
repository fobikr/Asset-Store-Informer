<div class="button nextButton" onclick="checkUASInfo();">Next</div>
<div>
    <h2>Asset store login/password:</h2>
    <div>
        <label for="uas-login">Username:</label>
        <input type="text" maxlength="60" size="60" value="" id="uas-login">
        <label for="uas-pass">Password:</label>
        <input type="password" maxlength="128" size="60" id="uas-pass">
        <div id="msgInfo" class="hidden"></div>
    </div>
</div>
<script>
    function checkUASInfo()
    {
        INSTALL.statusText($("#msgInfo"), "Trying to authorization in Unity Asset Store.");
        
        var login = $("#uas-login").val();
        var pass = $("#uas-pass").val();
        INSTALL.ajax("check-uas-info", {login: login, pass: pass}, function(result){
            if (result.status == "success")
            {
                INSTALL.setStep('mysql', 'MySQL settings');
            }
            else
            {
                INSTALL.statusText($("#msgInfo"), "Could not log into Unity Asset Store. Verify Login and Password.", true);
            }
        }, "json");
    }
    
    $("#uas-login").focus();
</script>