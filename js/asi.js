function ASI(){}

ASI.consts = {};
ASI.consts.hourInSeconds = 3600;
ASI.consts.dayInSecond = 86400;
ASI.consts.weekInSecond = 604800;

ASI.date = new Date();
ASI.minDate = ASI.date;
ASI.maxDate = ASI.date;
ASI.key = -1;
ASI.rootPath = "";
ASI.chartType = "sales";
ASI.updateChart = false;
ASI.updateSalesInfo = false;
ASI.updateSwitcher = false;
ASI.addLatestRowTypeClass = true;

ASI.ajax = function(action, params, callback, dataType)
{
    var props = {};
    if (params == undefined) params = {};
    
    for(p in params) 
    {
        if (!params.hasOwnProperty(p)) continue;
        props[p] = params[p];
    }
    
    props["action"] = action;
    props["key"] = ASI.key;
    
    var reqArgs = {
        type: "POST", 
        url: ASI.rootPath + "php/ajax.php", 
        data: props
    };
    
    if (dataType != undefined) reqArgs["dataType"] = dataType;
    
    var req = $.ajax(reqArgs);
    if (callback != undefined) req.done(callback);
};

ASI.href = function(path)
{
    window.location.href = ASI.rootPath + path;
};

ASI.DateFromString = function(str)
{
    str = str.split("/");
    if (str.length === 3) return new Date(str[2], str[1] - 1, str[0]);
    else if (str.length === 2) return new Date(str[1], str[0] - 1, 1);
    return "";
};

ASI.GetData = function(from, to, asset_id, callback, contentType)
{
    ASI.minDate = new Date(from * 1000);
    ASI.minDate.setMinutes(ASI.minDate.getMinutes() + ASI.minDate.getTimezoneOffset());
    if (to === 'NOW') 
    {
        ASI.maxDate = new Date();
        ASI.maxDate.setMinutes(ASI.maxDate.getTimezoneOffset());
        ASI.maxDate.setHours(12);
        ASI.maxDate.setSeconds(0);
        ASI.maxDate.setMilliseconds(0);
    }
    else 
    {
        ASI.maxDate = new Date(to * 1000);
        ASI.maxDate.setMinutes(ASI.maxDate.getMinutes() + ASI.maxDate.getTimezoneOffset());
    }
    var props = {from: from, to: to, asset_id: asset_id};
    if (contentType != null) props['contentType'] = contentType;
    ASI.ajax("getdata", props, callback);
};

ASI.LoadContent = function(module_name, target, type)
{
    target = typeof target !== 'undefined' ? target : $(".content-wrapper");
    ASI.ajax("getcontent", {name: module_name, type: type}, function(html){
        target.html(html);
    });
};

ASI.LoadModule = function(module_name, target)
{
    ASI.LoadContent(module_name, target, "module");
};

ASI.LoadView = function(view_name, target)
{
    ASI.LoadContent(view_name, target, "view");
};

ASI.Logout = function()
{
    ASI.ajax("logout", {}, function(){
        ASI.href("");
    });
};

ASI.VerifyInvoice = function (invoice)
{
    ASI.ajax("verify-invoice", {invoice: invoice}, function(html){
        var dlg = $("#dialog-verify-invoice-result");
        var result = JSON.parse(html);
        var text = "";
        var title = "";
        if (result.hasOwnProperty('status') && result.status === 'failed')
        {
            title = "Error";
            text = "When checking the invoice error occurred.";
        }
        else if (result.invoices.length === 0)
        {
            title = "Failed";
            text = "Invoices with this number does not exist.";
        }
        else
        {
            title = "Success";
            
            for(var i = 0; i < result.invoices.length; i++)
            {
                var inv = result.invoices[i];
                text += "<div>Invoice: " + inv.invoice + "</div>";
                text += "<div>Package: " + inv.package + "</div>";
                text += "<div>Date: " + inv.date + "</div>";
                text += "<div>Refunded: " + inv.refunded + "</div>";
            }
        }
        dlg.dialog( "option", "title", title );
        dlg.html(text);
        dlg.dialog( "open" );
    });
};

ASI.ApplyRaty = function ()
{
    $('.rating-star').each(function()
    {
        $(this).raty(
        { 
                readOnly: true, 
                score: $(this).attr('score'),
                starOff: ASI.rootPath + 'images/raty/star-off.png',
                starOn : ASI.rootPath + 'images/raty/star-on.png',
                noRatedMsg: "Not enough ratings"
        });
    });
};

String.prototype.capitalize = function() 
{
    return this.charAt(0).toUpperCase() + this.slice(1);
};

String.prototype.replaceAll = function(find, replace) {
    return this.replace(new RegExp(find, 'g'), replace);
}

Date.prototype.toUTC = function ()
{
    return new Date(this.getUTCFullYear(), this.getUTCMonth(), this.getUTCDate(),  this.getUTCHours(), this.getUTCMinutes(), this.getUTCSeconds());
}

function InitItems(result)
{
    var latest = ASI.latest = JSON.parse(result);
    var items = [];

    if (latest.sales)
    {
        for(var i = 0; i < latest.sales.length; i++)
        {
            var sale = latest.sales[i];
            var item = {
                time: sale.time,
                timestamp: Date.parse(sale.time.replace(" ", "T")),
                asset_id: sale.asset_id,
                offset: parseInt(sale.offset),
                price: parseInt(sale.price),
                type: "sale"
            };
            item.day = new Date(item.timestamp);
            item.day.setHours(3);
            item.day.setMinutes(0);
            item.day.setSeconds(0);
            item.day.setMilliseconds(0);
            items.push(item);
        }
    }

    if (latest.charges)
    {
        for(var i = 0; i < latest.charges.length; i++)
        {
            var charge = latest.charges[i];
            items.push({
                time: charge.time,
                timestamp: Date.parse(charge.time.replace(" ", "T")),
                asset_id: charge.asset_id,
                offset: parseInt(charge.offset),
                price: charge.price,
                type: "charge"
            });
        }
    }

    if (latest.refundings)
    {
        for(var i = 0; i < latest.refundings.length; i++)
        {
            var refunding = latest.refundings[i];
            items.push({
                time: refunding.time,
                timestamp: Date.parse(refunding.time.replace(" ", "T")),
                asset_id: refunding.asset_id,
                offset: parseInt(refunding.offset),
                price: refunding.price,
                type: "refunding"
            });
        }
    }

    if (latest.ratings)
    {
        for(var i = 0; i < latest.ratings.length; i++)
        {
            var rating = latest.ratings[i];
            items.push({
                time: rating.time,
                timestamp: Date.parse(rating.time.replace(" ", "T")),
                asset_id: rating.asset_id,
                offset: rating.offset,
                newrating: rating.newrating,
                oldrating: rating.oldrating,
                type: 'rating'
            });
        }
    }

    if (latest.reviews)
    {
        for(var i = 0; i < latest.reviews.length; i++)
        {
            var review = latest.reviews[i];
            var review_obj = {
                time: review.date,
                timestamp: Date.parse(review.date.replace(" ", "T")),
                asset_id: review.asset_id,
                subject: review.subject,
                full: review.full,
                rating: review.rating,
                user_name: review.user_name,
                user_id: review.user_id,
                type: 'review'
            };
            if (review.reply_subject != null) review_obj['reply_subject'] = review.reply_subject;
            if (review.reply_full != null) review_obj['reply_full'] = review.reply_full;
            if (review.reply_date != null) review_obj['reply_date'] = review.reply_date;
            items.push(review_obj);
        }
    }
    
    if (latest.events)
    {
        for(var i = 0; i < latest.events.length; i++)
        {
            var event = latest.events[i];
            items.push({
                time: event.time,
                timestamp: Date.parse(event.time.replace(" ", "T")),
                asset_id: event.asset_id,
                event_type: event.type,
                info: event.info,
                type: 'event'
            });
        }
    }

    ASI.items = items.sort(function (a, b)
    {
        if (a.timestamp < b.timestamp) return 1;
        else if (a.timestamp > b.timestamp) return -1;
        return 0;
    });
}

function OnGetLatest(result)
{
    InitItems(result);
    if (ASI.updateChart) 
    {
        if (ASI.chartType == "sales") UpdateSalesChart();
        else UpdateSummaryChart();
    }
    if (ASI.updateSalesInfo) UpdateSalesInfo();
    UpdateLatest();
}

function getSearchParameters() {
      var prmstr = window.location.search.substr(1);
      return prmstr != null && prmstr != "" ? transformToAssocArray(prmstr) : {};
}

function transformToAssocArray( prmstr ) {
    var params = {};
    var prmarr = prmstr.split("&");
    for ( var i = 0; i < prmarr.length; i++) {
        var tmparr = prmarr[i].split("=");
        params[tmparr[0]] = tmparr[1];
    }
    return params;
}

$(document).ready(function() 
{
    ASI.ApplyRaty();
});