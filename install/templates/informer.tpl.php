<div class="button nextButton" onclick="checkInformerInfo();">Next</div>
<div>
    <?php 
        print LoadCSS("combobox", "base"); 
        print LoadJS("combobox", "base");
    ?>
    <h2>Informer</h2>
    <div>
        <div>Send mail: </div>
        <div id="cbSendEmail">
            <span>Immediately</span>
            <ul class="dropdown">
                <li><a href="#">Immediately</a></li>
                <li><a href="#">Summary per time</a></li>
                <li><a href="#">Never</a></li>
            </ul>
        </div>
        <div id="blockSendFrequency" class="hidden">
            <label for="email-from">Frequency of sending mails:</label>
            <div id="cbFrequencySendEmail">
                <span>One hour</span>
                <ul class="dropdown">
                    <li sec="3600"><a>One hour</a></li>
                    <li sec="10800"><a>Three hours</a></li>
                    <li sec="21600"><a>Six hours</a></li>
                    <li sec="43200"><a>Twelve hours</a></li>
                    <li sec="86400"><a>One day</a></li>
                    <li sec="604800"><a>One week</a></li>
                </ul>
            </div>
        </div>
        <div id="email-div">
            <label for="email-from">Mail from:</label>
            <input type="text" maxlength="60" size="60" value="" id="email-from" placeholder="deamon@your-domain.com">
            <label for="email-to">Mail to:</label>
            <input type="text" maxlength="60" size="60" value="" id="email-to">
        </div>
    </div>
    
    <script>
        var cb = new ComboBox($("#cbSendEmail"));
        cb.change = function()
        {
            if (cb.getIndex() === 1) $("#blockSendFrequency").removeClass("hidden");
            else $("#blockSendFrequency").addClass('hidden');
            
            if (cb.getIndex() !== 2) $("#email-div").removeClass("hidden");
            else $("#email-div").addClass('hidden');
        }
        
        var cbFrequency = new ComboBox($("#cbFrequencySendEmail"));
        
        function checkInformerInfo()
        {
            var hasErrors = false;
            $("#email-from").removeClass("input-error");
            $("#email-to").removeClass("input-error");
            if (cb.getIndex() !== 2)
            {
                if ($("#email-from").val() == "" )
                {
                    hasErrors = true;
                    $("#email-from").addClass("input-error");
                }
                if ($("#email-to").val() == "")
                {
                    hasErrors = true;
                    $("#email-to").addClass("input-error");
                }
            }
            
            if (!hasErrors)
            {
                var data = {};
                
                data["mail-from"] = $("#email-from").val();
                data["mail-to"] = $("#email-to").val();
                data["freq"] = 0;
                
                if (cb.getIndex() == 2) data["freq"] = -1;
                else if (cb.getIndex() == 1) data["freq"] = $($("#cbFrequencySendEmail li")[cbFrequency.getIndex()]).attr("sec");
                
                INSTALL.ajax("set-informer", data, function(result){
                    if (result.status == "success")
                    {
                        INSTALL.setStep('progress', 'Installing');
                    }
                }, "json");
            }
        }
    </script>
</div>