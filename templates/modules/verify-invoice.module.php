<div id="dialog-verify-invoice" title="Verify invoice">
    <label for="invoice-num">Invoice number:</label>
    <input type="text" name="invoice-num" id="invoice-num" class="text ui-widget-content ui-corner-all">
</div>
<div id="dialog-verify-invoice-result" title="">
</div>

<script>
    $( "#dialog-verify-invoice" ).dialog({
        autoOpen: false,
        width: 350,
        modal: true,
        position: {
            my: "top+10",
            at: "top+10"
        },
        buttons: {
            "Verify": function() {
                var invoice = $("#dialog-verify-invoice #invoice-num").val();
                if (invoice == "") return;
                ASI.VerifyInvoice(invoice);
                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
    
    $( "#dialog-verify-invoice-result" ).dialog({
        autoOpen: false,
        modal: true,
        position: {
            my: "top+10",
            at: "top+10"
        }
    });
</script>