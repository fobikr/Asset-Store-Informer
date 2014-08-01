ASI.updateSalesInfo = true;

function UpdateSalesInfo()
{
    var totalContainer = $(".sales-info-total");
    var viewContainer = $(".sales-info-view");
    var detailContainer = $(".sales-info");

    var items = [];

    for(var i = 0; i < ASI.items.length; i++)
    {
        var item = ASI.items[i];
        if (item.type != 'sale' && item.type != 'charge' && item.type != 'refunding') continue;
        if (item.asset_id == "0") continue;

        var asset = ASI.assets[item.asset_id];
        var id = asset.title + item.price;

        if (items[id] == null) 
        {
            items[id] = {
                title: asset.title,
                path: asset.path,
                price: item.price,
                sale: 0,
                charge: 0,
                refunding: 0
            };
        }

        if (item.type == 'sale') items[id].sale += item.offset;
        else if (item.type == 'charge') items[id].charge += item.offset;
        else if (item.type == 'refunding') items[id].refunding += item.offset;
    }

    var tuples = [];
    for (var key in items) tuples.push(items[key]);

    items = tuples.sort(function(a, b){
        if (a.title < b.title) return -1;
        if (a.title > b.title) return 1;
        if (a.price < b.price) return -1;
        if (a.price > b.price) return 1;
        return 0;
    });

    $(".sales-info-detail-row").remove();

    var totalSales = 0;
    var totalMinus = 0;
    var totalGross = 0;

    var addRow = function (title, price, sale, gross, net, strong)
    {
        strong = typeof strong !== 'undefined'? strong: false;
        var row = "<div class='sales-info-detail-row'>";
        var titleClass = "sales-info-detail-row-title";
        if (strong) titleClass += " bold";
        row += "<div class='" + titleClass + "'>" + title + "</div>";
        row += "<div class='sales-info-detail-row-price'>" + price + "</div>";
        row += "<div class='sales-info-detail-row-count'>" + sale + "</div>";
        row += "<div class='sales-info-detail-row-gross'>" + gross + "$</div>";
        row += "<div class='sales-info-detail-row-net'>" + net + "$</div>";
        row += "</div>";
        detailContainer.append(row);
    }

    var addAssetRow = ASI.asset_id == "";

    for (var id in items)
    {
        var item = items[id];
        var minus = item.charge + item.refunding;
        var gross = (item.sale - minus) * item.price;
        var net = (gross * ASI.payout_cut).toFixed((gross % 10 == 0)?0: 1);

        totalSales += item.sale;
        totalMinus += minus;
        totalGross += gross;

        if (addAssetRow) 
        {
            var title = "<a path='" + item.path + "' assettitle='" + item.title + "'>" + item.title.replace(" ", "\u00a0") + "</a>";
            var sale = item.sale;
            if (minus > 0) sale += "-" + minus + "=" + (item.sale - minus);
            addRow(title, item.price + "$", sale, gross, net);
        }
    }

    var totalNet = (totalGross * ASI.payout_cut).toFixed((totalGross % 10 == 0)?0: 1);
    addRow("Total", "", totalSales + ((totalMinus > 0)? "-" + totalMinus + "=" + (totalSales - totalMinus): ""), totalGross, totalNet, true);
    
    if (ASI.latest.month)
    {
        var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
        
        var monthi = parseInt(ASI.latest.month.month_id.substr(4, 2)) - 1;
        var month = monthNames[monthi];
        var year = ASI.latest.month.month_id.substr(0, 4);
        
        totalSales = parseInt(ASI.latest.month.sales);
        totalMinus = parseInt(ASI.latest.month.refundings) + parseInt(ASI.latest.month.charges);
        totalGross = parseInt(ASI.latest.month.total);
        totalNet = (totalGross * ASI.payout_cut).toFixed((totalGross % 10 == 0)?0: 1);
        var totalCount = totalSales - totalMinus;
        var expectedGross = parseInt(ASI.latest.expected.gross);
        var expectedCount = parseInt(ASI.latest.expected.count);
        
        var totalDays = new Date(year, monthi + 1, 0).getDate();
        var curDay = new Date(ASI.date);
        curDay.setMinutes(curDay.getMinutes() + curDay.getTimezoneOffset());
        var curMonth = curDay.getMonth();
        var curYear = curDay.getFullYear();
        curDay = curDay.getDate() - 1;
        if (curDay <= 0) curDay = 1;
        
        var calcGross = Math.round(totalGross / curDay * totalDays);
        var calcCount = Math.round((totalSales - totalMinus) / curDay * totalDays);
        
        if (curMonth != monthi)
        {
            expectedCount = expectedCount;
            expectedGross = expectedGross;
        }
        else if (curDay > 19 || totalGross > expectedGross || totalCount > expectedCount)
        {
            expectedCount = calcCount;
            expectedGross = calcGross;
        }
        else if (curDay > 9)
        {
            var countOffset = expectedCount - calcCount;
            var grossOffset = expectedGross - calcGross;
            expectedCount = Math.round(expectedCount - countOffset / (20 - curDay));
            expectedGross = Math.round(expectedGross - grossOffset / (20 - curDay));
        }
        
        var expectedNet = (expectedGross * ASI.payout_cut).toFixed((expectedGross % 10 == 0)?0: 1);
        var expectedMonth = monthNames[curMonth];
        
        addRow(month + " " + year, "", totalSales + ((totalMinus > 0)? "-" + totalMinus + "=" + totalCount: ""), totalGross, totalNet, true);
        addRow("Expected in " + expectedMonth + " " + curYear, "", expectedCount, expectedGross, expectedNet, true);
    }

    $(".sales-info-detail-row a").click(OnLatestAssetClick);
}