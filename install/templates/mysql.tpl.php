<div class="button nextButton" onclick="checkDBInfo();">Next</div>
<div>
    <h2>MySQL settings:</h2>
    <div>
        <label for="db-server">Server:</label>
        <input type="text" maxlength="60" size="60" value="localhost" id="db-server">
        <label for="db-name">Database name:</label>
        <input type="text" maxlength="60" size="60" value="" id="db-name">
        <label for="db-login">Login:</label>
        <input type="text" maxlength="60" size="60" value="root" id="db-login">
        <label for="db-password">Password:</label>
        <input type="text" maxlength="60" size="60" value="" id="db-password">
        <label for="db-prefix">Table prefix:</label>
        <input type="text" maxlength="60" size="60" value="asi_" id="db-prefix">
        <div id="msgInfo" class="hidden"></div>
    </div>
    <script>
        $("#db-server").focus();
        
        function checkDBInfo()
        {
            INSTALL.statusText($("#msgInfo"), "Checking the connection to the database.");
        
            var server = $("#db-server").val();
            var name = $("#db-name").val();
            var login = $("#db-login").val();
            var password = $("#db-password").val();
            var prefix = $("#db-prefix").val();
            
            INSTALL.ajax("check-db-info", {server: server, name: name, login: login, pass: password, prefix: prefix}, function(result){
                try {
                    result = $.parseJSON(result);
                    if (result != null && result.status == "success")
                    {
                        INSTALL.setStep('login', 'Login');
                    }
                    else
                    {
                        INSTALL.statusText($("#msgInfo"), "Could not connect to the database. Check the settings.", true);
                    }
                }
                catch(e)
                {
                    INSTALL.statusText($("#msgInfo"), "Could not connect to the database. Check the settings.", true);
                }
            });
        }
    </script>
</div>