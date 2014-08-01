var hasErrors = false;
var msgError = "";

function clearError(field)
{
    $(field).removeClass("input-error");
}

function setError(field, msg)
{
    $(field).addClass("input-error");
    hasErrors = true;
    if (msg != undefined)
    {
        if (msgError != "") msgError += "<br/>";
        msgError += msg;
    }
}

$("#btnChangePassword").click(function(){
    hasErrors = false;
    msgError = "";
    
    clearError("#old-password");
    clearError("#new-password");
    clearError("#new-password-2");
    
    if ($("#old-password").val() == "") setError ("#old-password");
    if ($("#new-password").val() == "") setError ("#new-password");
    if ($("#new-password-2").val() == "") setError ("#new-password-2");
    if (!hasErrors && $("#new-password").val() != $("#new-password-2").val()) 
    {
        setError("#new-password");
        setError("#new-password-2");
    }
    
    if (hasErrors) return;
    
    var p1 = $.sha256($("#old-password").val() + openkey);
    var p2 = $.sha256($("#new-password").val() + openkey);
    
    ASI.ajax("change-password", {login: login, oldpass: p1, newpass: p2}, function (response){
        if (response.status == 'success') ASI.href("settings");
        else showMessage('Old password is incorrect.');
    }, "json");
});