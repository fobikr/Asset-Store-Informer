<div class="button nextButton" onclick="checkLoginInfo();">Next</div>
<div>
    <h2>Login</h2>
    <div>
        <label for="asi-login">Login:</label>
        <input type="text" maxlength="60" size="60" value="" id="asi-login">
        <label for="asi-password">Password:</label>
        <input type="password" maxlength="60" size="60" value="" id="asi-password" placeholder="password">
        <label for="asi-password2">Re-enter password:</label>
        <input type="password" maxlength="60" size="60" value="" id="asi-password2" placeholder="password">
    </div>
    <?php 
        print LoadCSS("jquery-passfield", "base", FALSE);
        print LoadJS("jquery.passfield", "base");
        print LoadJS("jquery.passfield.locales", "base");
        print LoadJS("jquery.sha256.min", "base");
    ?>
    <script>
        $("#asi-login").focus();
        
        var passfield = $("#asi-password").passField({
            allowEmpty: false,
            showWarn: false,
            showTip: false,
            strengthCheckTimeout: 100,
            locale: "en"
        });
        
        var openkey = "<?php print $_SESSION['asi_install']['open_key']; ?>";
        
        function checkLoginInfo()
        {
            var hasErrors = false;
            $("#asi-login").removeClass("input-error");
            $("#asi-password").removeClass("input-error");
            $("#asi-password2").removeClass("input-error");
            
            if ($("#asi-login").val() == "")
            {
                hasErrors = true;
                $("#asi-login").addClass("input-error");
            }
            
            if ($("#asi-password").val() != $("#asi-password2").val() || $("#asi-password2").val() == "")
            {
                hasErrors = true;
                $("#asi-password").addClass("input-error");
                $("#asi-password2").addClass("input-error");
            }
            
            if (!hasErrors) 
            {
                var pass = $.sha256($("#asi-password").val() + openkey);
                INSTALL.ajax("set-login", {
                    login: $("#asi-login").val().toLowerCase(),
                    pass: pass
                }, function(result){
                    if (result.status === "success") INSTALL.setStep('informer', 'Informer');
                }, "json");
            }
        }
    </script>
</div>