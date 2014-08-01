function getMonday(d) 
{
    d = new Date(d);
    var day = d.getDay(),
    diff = d.getDate() - day + (day === 0 ? -6:1);
    return new Date(d.setDate(diff));
}

function getWeek(day)
{
    var bDate = getMonday(day);
    bDate.setHours(0);
    bDate.setMinutes(0);
    bDate.setSeconds(0);
    bDate.setMilliseconds(0);
    var eDate = new Date(bDate.getTime());
    eDate.setDate(eDate.getDate() + 6);
    return bDate.format("dd/mm/yyyy") + " - " + eDate.format("dd/mm/yyyy");
}

function onGetLast7Days()
{
    var oneWeekAgo = new Date();
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
    oneWeekAgo.setMinutes(oneWeekAgo.getTimezoneOffset());
    oneWeekAgo.setHours(0);
    oneWeekAgo.setSeconds(0);
    oneWeekAgo.setMilliseconds(0);
    ASI.GetData(oneWeekAgo.getTime() / 1000, 'NOW', ASI.asset_id, OnGetLatest);
}

function onDateRangeSelectorAClick()
{
    var $this = $(this);
    if ( $this.hasClass('selected') ) return false;
    var $optionSet = $this.parents('.option-set');
    $optionSet.find('.selected').removeClass('selected');
    $this.addClass('selected'); 

    var dov = $this.attr("data-option-value");
    
    toggleDataRangeSelectorBlocks(dov);
    
    if (dov === "last7days") onGetLast7Days();
    else if (dov === "week") onDateRangeSelectorChangeWeek();
    else if (dov === "month") onDateRangeSelectorChangeMonth();
    else if (dov === "year") onDateRangeSelectorChangeYear();
}

function onDateRangeSelectorTypeAClick()
{
    var $this = $(this);
    if ( $this.hasClass('selected') ) return false;
    
    var $optionSet = $this.parents('.option-set');
    $optionSet.find('.selected').removeClass('selected');
    $this.addClass('selected'); 
    
    var dov = $this.attr("data-option-value");
    
    if (dov == "sales")
    {
        UpdateSalesChart();
    }
    else
    {
        ASI.chartType = dov;
        UpdateSummaryChart();
    }
}

function toggleDataRangeSelectorBlocks(dov)
{
    ASI.params.range = dov;
    $(".date-range-selector-week").css("display", (dov === "week")? "block": "none");
    $(".date-range-selector-month").css("display", (dov === "month")? "block": "none");
    $(".date-range-selector-year").css("display", (dov === "year")? "block": "none");
    $(".date-range-selector-custom").css("display", (dov === "custom")? "block": "none");
}

function onDateRangeSelectorChangeYear()
{
    var year = parseInt(ASI.dataSelector.yearCombobox.getValue());
    
    ASI.params.period = year;
    
    var dayStart = new Date(year, 0, 1);
    var dayEnd = new Date(year + 1, 0, 1);
    dayStart = dayStart / 1000;
    dayEnd = dayEnd / 1000;
    ASI.GetData(dayStart, dayEnd, ASI.asset_id, OnGetLatest);
}

function onDateRangeSelectorChangeMonth()
{
    ASI.params.period = $(".monthpicker").val();
    
    var dayStart = ASI.DateFromString(ASI.params.period);
    var dayEnd = 0;
    if (dayStart.getMonth() === 11) dayEnd = new Date(dayStart.getFullYear() + 1, 0, 1);
    else dayEnd = new Date(dayStart.getFullYear(), dayStart.getMonth() + 1, 1);
    dayStart = dayStart / 1000;
    dayEnd = dayEnd / 1000;
    ASI.GetData(dayStart, dayEnd, ASI.asset_id, OnGetLatest);
}

function onDateRangeSelectorChangeWeek()
{
    var picker = $(".weekpicker");
    if (picker.datepicker('getDate') != undefined) picker.val(getWeek(picker.datepicker('getDate')));
    
    var days = picker.val().split("-");
    
    ASI.params.period = days[0];
    
    var day = ASI.DateFromString(days[0]) / 1000;
    ASI.GetData(day, day + ASI.consts.weekInSecond, ASI.asset_id, OnGetLatest);
}

function onDateRangeSelectorCustom()
{
    var from = ASI.DateFromString($("#date-range-selector-custom-from").val()) / 1000;
    var to = ASI.DateFromString($("#date-range-selector-custom-to").val()) / 1000 + ASI.consts.dayInSecond;
    ASI.GetData(from, to, ASI.asset_id, OnGetLatest);
}

monthpickeroptions = {
    selectedYear: ASI.date.getFullYear(),
    startYear: ASI.firstYear,
    finalYear: ASI.maxDate.getFullYear()
};

$('.weekpicker').datepicker( {
    showOtherMonths: true,
    selectOtherMonths: true,
    selectWeek: true,
    changeMonth: true,
    changeYear: true,
    firstDay: 1,
    dateFormat: "dd/mm/yy",
    onClose: onDateRangeSelectorChangeWeek
});

$( "#date-range-selector-custom-from" ).datepicker({
    defaultDate: "+1w",
    changeMonth: true,
    changeYear: true,
    firstDay: 1,
    dateFormat: "dd/mm/yy",
});

$( "#date-range-selector-custom-to" ).datepicker({
    defaultDate: "+1w",
    changeMonth: true,
    changeYear: true,
    firstDay: 1,
    dateFormat: "dd/mm/yy",
});

var d = new Date(ASI.date);
d.setMinutes(d.getMinutes() + d.getTimezoneOffset());

ASI.dataSelector = {};
ASI.dataSelector.yearCombobox = new ComboBox($("#date-range-selector-year-combobox"));
ASI.dataSelector.yearCombobox.setValue(d.getFullYear());
ASI.dataSelector.yearCombobox.change = onDateRangeSelectorChangeYear;

$(".daypicker").val(d.format("dd/mm/yyyy"));
$(".weekpicker").val(getWeek(d));
$('.monthpicker').val(d.format("mm/yyyy"));

$("#date-range-selector-custom-show").click(onDateRangeSelectorCustom);
$("#date-range-selector-options-section .toggle-button").click(onDateRangeSelectorAClick);
$("#date-range-selector-options-type .toggle-button").click(onDateRangeSelectorTypeAClick);
$('.monthpicker').monthpicker(monthpickeroptions).bind('monthpicker-click-month', onDateRangeSelectorChangeMonth);

$(document).ready(function() 
{
    var params = ASI.params = getSearchParameters();
    
    var range = "last7days";
    if (params.hasOwnProperty("range")) range = params["range"];
    
    if (range === "week")
    {
        if (params.hasOwnProperty("period")) $(".weekpicker").val(getWeek(ASI.DateFromString(params["period"])));
        onDateRangeSelectorChangeWeek();
    }
    else if (range === "month")
    {
        if (params.hasOwnProperty("period")) $('.monthpicker').val(params["period"]);
        onDateRangeSelectorChangeMonth();
    }
    else if (range === "year")
    {
        if (params.hasOwnProperty("period")) ASI.dataSelector.yearCombobox.setValue(params["period"]);
        onDateRangeSelectorChangeYear();
    }
    else 
    {
        range = "last7days";
        onGetLast7Days();
    }

    $("#date-range-selector-options-section .selected").removeClass("selected");
    $("#date-range-selector-options-section .toggle-button[data-option-value='" + range + "']").addClass("selected");

    toggleDataRangeSelectorBlocks(range);
});