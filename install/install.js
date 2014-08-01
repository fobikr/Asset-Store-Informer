function INSTALL(){}

INSTALL.rootPath = "";
INSTALL.defTitle = document.title;
NavigationCache = new Array();

INSTALL.addHistory = function(data)
{
    if (history.pushState)
    {
        history.pushState({id: NavigationCache.length}, document.title, document.location.href);
        NavigationCache[NavigationCache.length] = data;
    }
}

function installOnKeyPress(e)
{
    if (e.keyCode == 13) {
        $(".nextButton").click();
        return false;
    }
}

INSTALL.ajax = function(action, params, callback, dataType)
{
    var props = {};
    
    for(p in params) 
    {
        if (!params.hasOwnProperty(p)) continue;
        props[p] = params[p];
    }
    
    props["action"] = action;
    props["key"] = INSTALL.key;
    
    var reqArgs = {
        type: "POST", 
        url: INSTALL.rootPath + "install/ajax.php", 
        data: props
    };
    
    if (dataType != undefined) reqArgs["dataType"] = dataType;
    
    var req = $.ajax(reqArgs);
    if (callback != undefined) req.done(callback);
}

INSTALL.setStep = function (id, title)
{
    $.ajax({
        type: "POST", 
        url: INSTALL.rootPath + "install/setStep.php",
        data: {step: id, key: INSTALL.key}
    }).done(function(html){
        INSTALL.addHistory(html);
        $(".install-window").html(html);
        if (title != undefined) document.title = INSTALL.defTitle + " - " + title;
        else document.title = INSTALL.defTitle;
    });
}

INSTALL.statusText = function (field, text, isError)
{
    if (isError == null) isError = false;
    
    field.text(text);
    field.removeClass("hidden");
    field.addClass(isError? "redText": "greenText");
    field.removeClass(isError? "greenText": "redText");
}

$(document).ready(function()
{
    if (history.pushState) 
    {
        window.onpopstate = function(event) 
        {
            if (event.state != null && event.state.id != undefined) $('.install-window').html(NavigationCache[event.state.id]);
            else $('.install-window').html(NavigationCache[0]);
        }
        NavigationCache[0] = $(".install-window").html();
    }
});