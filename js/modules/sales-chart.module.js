ASI.updateChart = true;

function onSeriesLegendItemClick(event)
{
    var needShow = !this.visible;
    
    $(".latest-asset a[assettitle='" + this.userOptions.name + "']").each(function( index, value ){
        $(this).parent().attr("v", needShow);
    });
    
    UpdateLatestVisible();
}
        
function UpdateSalesChart()
{
    var series = [];
    var seriesCount = 0;
    for(var i = 0; i < ASI.items.length; i++)
    {
        var item = ASI.items[i];
        if (item.type != 'sale') continue;
        var asset = ASI.assets[item.asset_id];

        var id = asset.title;
        if (series[id] == null)
        {
            series[id] = {
                name: asset.title,
                data: []
            };
            seriesCount++;
        }
        if (series[id].data.length > 0 && series[id].data[series[id].data.length - 1][0] == item.day.getTime())
        {
            series[id].data[series[id].data.length - 1][1] += item.offset;
        }
        else 
        {
            series[id].data.push([
                item.day.getTime(),
                item.offset
            ]);
        }
    }

    if (seriesCount == 0)
    {
        var title = ASI.isAsset? ASI.assets[ASI.asset_id].title: "Nothing";
        series.push({
            name: title,
            data: []
        });
    }

    var tuples = [];
    for (var key in series) tuples.push(series[key]);

    series = tuples.sort(function(a, b){
        if (a.title < b.title) return -1;
        if (a.title > b.title) return 1;
        if (a.price < b.price) return -1;
        if (a.price > b.price) return 1;
        return 0;
    });

    for (var i = 0; i < series.length; i++)
    {
        series[i].data = series[i].data.sort(function(a, b){
            if (a[0] < b[0]) return -1;
            if (a[0] > b[0]) return 1;
            return 0;
        });
    }
    
    $('#sales-chart-container').iccharts({
        chart: {
            type: 'column'
        },
        plotOptions: {
            series: {
                grouping: false,
                pointRange: 24 * 3600 * 1000,
                stacking: 'normal',
                events: {
                    legendItemClick: onSeriesLegendItemClick
                }
            },
            column: {
                stacking: 'normal'
            }
        },
        series: series,
        title: {
            text: 'Sales'
        },
        tooltip: {
            shared: true,
            valueSuffix: ' items',
            xDateFormat: '%d-%m-%Y',
        },
        xAxis: {
            type: 'datetime',
            title: {
                enabled: false
            },
            min: ASI.minDate.getTime(),
            max: ASI.maxDate.getTime() - ASI.maxDate.getTimezoneOffset() * 60000 - ASI.consts.hourInSeconds * 12 * 1000
        },
        yAxis: {
            title: {
                text: 'Count'
            },
            labels: {
                formatter: function() {
                    return this.value;
                }
            },
            allowDecimals: false,
            showFirstLabel: true
        }
    });
}