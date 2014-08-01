<?php 
    print LoadCSS("settings", "view");
    print LoadJS('jquery.sha256.min', 'base');
?>

<script> 
    var openkey="<?php print $_SESSION['auth-key']; ?>";
    var login = "<?php print $_SESSION['user']; ?>";
</script>

<div id="settings-wrapper">
    <h2>Change password</h2>
    <div>
        <label for="old-password">Old password:</label>
        <input type="password" maxlength="60" size="60" value="" id="old-password">
        <label for="new-password">New password:</label>
        <input type="password" maxlength="60" size="60" value="" id="new-password">
        <label for="new-password-2">Confirm new password:</label>
        <input type="password" maxlength="60" size="60" value="" id="new-password-2">
    </div>
    <div id="btnChangePassword" class="button">
        <a>Change password</a>
    </div>
</div>

<?php print LoadJS("change-password", "view"); ?>