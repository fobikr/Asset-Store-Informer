<div>
    <h2>Installing ...</h2>
    <div>
        <div id="progressbar"><div class="progress-label">Loading...</div></div>
    </div>
    <div id="progress-msg"></div>
    <script>
        $( "#progressbar" ).progressbar({
            value: false
        });
        $( "#progressbar" ).progressbar( "option", "value", false );
        $( "#progressbar .ui-progressbar-value" ).css({
            "background": '#9DCE2C'
        });
        
        function setMessage(msg, isError)
        {
            if (isError == null) isError = false;
            
            var field = $("#progress-msg");
            
            field.text(msg);
            
            if (isError) field.addClass("redText");
            else field.removeClass("redText");
        }
        
        function progress(val)
        {
            INSTALL.progress = val;
            $("#progressbar").progressbar( "option", "value", val );
            $(".progress-label").text(Math.round(val) + " %");
        }
        
        function createTablesComplete(result)
        {
            if (result.status == "success")
            {
                progress(5);
                INSTALL.ajax("create-publisher-info", {}, createPublisherInfoComplete, "json");
                setMessage("Get publisher info");
            }
            else setMessage("Can not create a MySQL table.", true)
        }
        
        function createPublisherInfoComplete(result)
        {
            if (result.status == "success")
            {
                progress(10);
                INSTALL.periods = result.periods;
                INSTALL.periods.reverse();
                INSTALL.periods_progress = 20.00 / INSTALL.periods.length;
                INSTALL.ajax("get-assets", {}, getAssetsComplete, "json");
                setMessage("Get a list of assets.");
            }
            else
            {
                setMessage("Can not get publisher info.", true)
            }
        }
        
        function getAssetsComplete(result)
        {
            if (result.status == "success")
            {
                progress(15);
                INSTALL.assets = result.assets;
                INSTALL.progress_index = 0;
                INSTALL.asset_progress = 20.00 / INSTALL.assets.length;
                getNextAssetInfo();
            }
            else setMessage("Can not get list of assets.", true)
        }
        
        function getNextAssetInfo()
        {
            var asset = INSTALL.assets[INSTALL.progress_index];
            setMessage("Get asset info: " + asset.title);
            INSTALL.ajax("get-asset-info", {asset: asset.id, hotness: asset.hotness}, getNextAssetInfoComplete, "json");
        }
        
        function getNextAssetInfoComplete(result)
        {
            if (result.status == "success")
            {
                progress(INSTALL.progress + INSTALL.asset_progress);
                INSTALL.progress_index++;
                if (INSTALL.progress_index < INSTALL.assets.length) 
                {
                    getNextAssetInfo();
                }
                else
                {
                    INSTALL.progress_index = 0;
                    getNextSalesPeriod();
                }
            }
            else setMessage("Can not get asset info: " + INSTALL.assets[INSTALL.progress_index].title, true);
        }
        
        function period2String(period)
        {
            var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            
            var year = period.substr(0, 4);
            var month = parseInt(period.substr(4, 2)) - 1;
            
            return months[month] + " " + year;
        }
        
        function getNextSalesPeriod()
        {
            var period = INSTALL.periods[INSTALL.progress_index];
            setMessage("Get sales period: " + period2String(period));
            INSTALL.ajax("get-sales-period", {period: period}, getNextSalesPeriodComplete, "json");
        }
        
        function getNextSalesPeriodComplete(result)
        {
            if (result.status == "success")
            {
                progress(INSTALL.progress + INSTALL.periods_progress);
                INSTALL.progress_index++;
                if (INSTALL.progress_index < INSTALL.periods.length) 
                {
                    getNextSalesPeriod();
                }
                else
                {
                    INSTALL.progress_index = 0;
                    getNextAssetComments();
                }
            }
            else setMessage("Can not get sales period: " + period2String(INSTALL.periods[INSTALL.progress_index]), true);
        }
        
        function getNextAssetComments()
        {
            var asset = INSTALL.assets[INSTALL.progress_index];
            setMessage("Get asset reviews: " + asset.title);
            INSTALL.ajax("get-asset-comments", {asset: asset.id, rating_count: asset.rating_count, rating_average: asset.rating_average}, getNextAssetCommentsComplete, "json");
        }
        
        function getNextAssetCommentsComplete(result)
        {
            if (result.status == "success")
            {
                progress(INSTALL.progress + INSTALL.asset_progress);
                INSTALL.progress_index++;
                if (INSTALL.progress_index < INSTALL.assets.length) 
                {
                    getNextAssetComments();
                }
                else
                {
                    finish();
                }
            }
            else setMessage("Can not get asset reviews: " + asset.title, true);
        }
        
        function finish()
        {
            progress(99);
            setMessage("Finish the installation.");
            INSTALL.ajax("finish", {}, finishComplete, "json");
        }
        
        function finishComplete(result)
        {
            if (result.status == "success")
            {
                INSTALL.setStep('finish', 'Finish');
            }
            else setMessage("Can not finish the installation.", true)
        }
        
        INSTALL.ajax("create-tables", {}, createTablesComplete, "json");
        setMessage("Init MySQL");
    </script>
</div>