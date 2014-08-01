<?php 
    print LoadCSS('login', 'view'); 
    print LoadJS('jquery.sha256.min', 'base');
    print LoadJS('jquery.cookie', 'base');
?>

<script> 
    var openkey="<?php print $_SESSION['auth-key']; ?>"; 
</script>

<div class="login div-center shadow" width="460px">
    <h2>Login</h2>
    <div>
        <form id="login-form" onkeypress="return loginOnKeyPress(event)">
            <div>
                <label for="login-name">Username <span title="This field is required." class="form-required">*</span></label>
                <input type="text" class="required" maxlength="60" size="60" id="login-name" lang="en">
            </div>
            <div>
                <label for="login-pass">Password <span title="This field is required." class="form-required">*</span></label>
                <input type="password" maxlength="128" size="60" id="login-pass" lang="en" onkeypress="detectCapsLock(event)">
            </div>
            <div id="passCapsWarning" style="display:none">Caps Lock is on.</div>
            <div id="login-message"></div>
            <div>
                <div class="button" onclick="ajaxLogin();">Log in</div>
            </div>
        </form>
    </div>
</div>

<?php print LoadJS('login', 'view'); ?>