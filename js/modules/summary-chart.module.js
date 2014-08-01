function UpdateSummaryChart()
{
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
                price: item.price,
                count: 0,
            };
        }

        if (item.type == 'sale') items[id].count += item.offset;
        else items[id].count -= item.offset;
    }

    var series = [];

    for(var id in items)
    {
        if (items[id].count <= 0) continue;
        var val = items[id].count;
        if (ASI.chartType == "summary-gross") val *= items[id].price;
        series.push([items[id].title, val]);
    }
    
    var pointFormat = "";
    if (ASI.chartType == "summary-count") pointFormat = '{series.name}: {point.y} items, <b>{point.percentage:.1f}%</b>';
    else pointFormat = '{series.name}: {point.y} $, <b>{point.percentage:.1f}%</b>';
    
    var title = (ASI.chartType == "summary-count")?"count": "gross";

    $('#sales-chart-container').iccharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
        },
        title: {
            text: 'Summary sales (' + title + ')'
        },
        tooltip: {
            pointFormat: pointFormat
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                },
            }
        },
        series: [{
            type: 'pie',
            name: title.capitalize(),
            data: series
        }]
    });
}