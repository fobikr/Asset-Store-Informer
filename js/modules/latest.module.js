function OnLatestAssetClick()
{
    var url = "assets/" + $(this).attr("path");
    
    if (ASI.params)
    {
        var range = ASI.params.range;

        if (range === "week" || range === "month" || range === "year")
        {
            url += "?range=" + range;
            if (ASI.params.hasOwnProperty("period")) url += "&period=" + ASI.params.period;
        }
    }

    ASI.href(url);
}

function UpdateLatest()
{
    $(".latest-row").remove();

    var container = $(".latest");
    var initStars = false;
    var odd = true;

    for(var i = 0; i < ASI.items.length; i++)
    {
        var item = ASI.items[i];
        var rowClasses = "latest-row";
        if (ASI.addLatestRowTypeClass) rowClasses += " latest-row-" + item.type;
        rowClasses += " " + (odd?"odd":"even");
        
        var row = "<div class='" + rowClasses + "'>";
        row += "<div class='latest-type' typeid='" + item.type.capitalize() + "s'>" + item.type.capitalize() + "</div>";
        row += "<div class='latest-date'>" + item.time + "</div>";
        
        if (ASI.isAsset){}
        else if (item.asset_id != "0") 
        {
            var asset = ASI.assets[item.asset_id];
            row += "<div class='latest-asset'><a path='" + asset.path + "' assettitle='" + asset.title + "'>" + asset.title.replace(" ", "\u00a0") + "</a></div>";
        }
        else row += "<div class='latest-asset'>Unknown</div>";
        
        row += "<div class='latest-info'>";
        
        if (item.type == 'review') 
        {
            row += "<div class='bold'>" + item.subject + "</div>";
            row += "<div>by <a href='https://www.assetstore.unity3d.com/#/user/" + item.user_id + "' target='_blank'>" + item.user_name + "</a></div>"
            if (item.rating != null)
            {
                row += "<div>";
                row += "<div class='inline bold'>Rating:</div>";
                row += "<div class='rating-star inline' score='" + item.rating + "'></div>";
                row += "</div>";
                initStars = true;
            }
            row += "<div>" + item.full.replaceAll("\n", "<br/>") + "</div>";
            if (item.reply_full != null)
            {
                row += "<hr class='faded'/>";
                row += "<div class='review-reply'>";
                row += "<div class='review-reply-title'>" + item.reply_subject.replaceAll("\n", "<br/>") + "</div>";
                row += "<div>" + item.reply_date + "</div>";
                row += "<div class='review-reply-body'>" + item.reply_full.replaceAll("\n", "<br/>") + "</div>";
                row += "</div>";
            }
        }
        else if (item.type == 'rating')
        {
            if (item.newrating != 0) 
            {
                row += "<div>";
                row += "<div class='inline'>Now asset rating: </div>";
                row += "<div class='rating-star inline' score='" + item.newrating + "'></div>";
                row += "</div>";
                initStars = true;
            }
        }
        else if (item.type == 'event')
        {
            if (item.event_type == 'version_changed') row += "The new version (" + item.info + ") has been accepted.";
        }
        else if (item.type == 'sale') row += item.offset + " x " + item.price + " = " + (item.offset * item.price) + "$";
        else if (item.type == 'charge' || item.type == 'refunding') row += "-" + item.offset + " x " + item.price + " = -" + (item.offset * item.price) + "$";
        
        row += "</div>"
        row += "</div>";
        container.append(row);
        odd = !odd;
    }
    
    $(".latest-asset a").click(OnLatestAssetClick);
    if (ASI.updateSwitcher) UpdateLatestSwitcher();
    if (initStars) ASI.ApplyRaty();
}

function UpdateLatestVisible()
{
    $(".latest-row").each(function( index, value ){
        var countHidden = $(this).find("*[v='false']").length;
        if (countHidden == 0) $(this).show();
        else $(this).hide();
    });
}