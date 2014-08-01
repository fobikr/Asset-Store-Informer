var cbMail = new ComboBox($("#cbSendEmail"));
var cbFrequency = new ComboBox($("#cbFrequencySendEmail"));

cbMail.change = function()
{
    if (cbMail.getIndex() === 1) $("#blockSendFrequency").removeClass("hidden");
    else $("#blockSendFrequency").addClass("hidden");
    
    if (cbMail.getIndex() !== 2) $("#blockSendInfo").removeClass("hidden");
    else $("#blockSendInfo").addClass("hidden");
}

$("#btnUpdateInvoiceKey").click(function(){
    ASI.ajax("update-invoice", {}, function(){
    })
});

$("#btnChangePassword").click(function(){
    ASI.href("settings/change-password");
});

$("#btnSave").click(function(){
    var params = {};
    
    params["mail-from"] = $("#email-from").val();
    params["mail-to"] = $("#email-to").val();
    params["freq"] = 0;
    
    if (cbMail.getIndex() == 2) params["freq"] = -1;
    else if (cbMail.getIndex() == 1) params["freq"] = $($("#cbFrequencySendEmail li")[cbFrequency.getIndex()]).attr("sec");
    
    ASI.ajax("save-settings", params, function (){
        ASI.href("");
    });
})