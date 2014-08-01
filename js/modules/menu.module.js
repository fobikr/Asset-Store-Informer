function onMenuSettingsClick() {
    ASI.href("settings");
}

function ShowInvoiceForm (){
    $( "#dialog-verify-invoice" ).dialog( "open" );
}

function ShowUpdates() {
    $( "#dialog-updates" ).dialog( "open" );
}

$("#menu-settings").click(onMenuSettingsClick);