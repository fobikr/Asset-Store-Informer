function showMessage(msg)
{
    $("#login-message").text(msg);
}

function ajaxLogin()
{
    $("#login-message").html('');
    
    var login = $("#login-name");
    var pass = $("#login-pass");
    
    if ( login.val() == '' ) login.addClass('input-error');
    else login.removeClass('input-error');
    if ( pass.val() == '' ) pass.addClass('input-error');
    else pass.removeClass('input-error');
    
    if (login.val() !== '' && pass.val() !== '')
    {
        var p = $.sha256(pass.val() + openkey);
        
        $.cookie("login", login.val().toLowerCase(), { expires : 30 });
        
        $.ajax({
            type: "POST", 
            url: "php/login.php", 
            data: {l: login.val(), p: p, r: Math.random() },
            dataType: "json"
        }).done(function(response)
        {
            if (response.result == 'success') location.reload();
            else showMessage('Login or password is incorrect.');
        });
    }
    else
    {
        showMessage('Login or password is incorrect.');
    }
}

function loginOnKeyPress(e)
{
    if (e.keyCode == 13) {
        ajaxLogin();
        return false;
    }
}

function detectCapsLock(e)
{
    var kc = e.keyCode?e.keyCode:e.which;
    var sk = e.shiftKey?e.shiftKey:((kc == 16)?true:false);
    var isCaps = ((kc >= 65 && kc <= 90) && !sk)||((kc >= 97 && kc <= 122) && sk);
    $("#passCapsWarning").css("display", isCaps? "block": "none");
}

$("#login-name").val($.cookie("login"));