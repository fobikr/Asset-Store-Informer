ASI.updateSwitcher = true;

$(".cmn-toggle-yes-no").change(function(event)
{
    var typeid = $(this).next().attr("data-on");
    var selected = $(this).prop("checked");
    $(".latest-type[typeid='" + typeid + "']").attr("v", selected);
    UpdateLatestVisible();
});

function UpdateLatestSwitcher()
{
    $(".latest-switcher input").each(function()
    {
        var switcher = $(this);
        var typeid = switcher.next().attr("data-on");
        var selected = switcher.prop("checked");
        $(".latest-type[typeid='" + typeid + "']").attr("v", selected);
    });
    
    UpdateLatestVisible();
}