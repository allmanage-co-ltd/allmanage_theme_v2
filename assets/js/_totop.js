/*********************************************
 * ToTop
********************************************/
$(function () {
    const totop = $("#js-totop").hide();

    $(window).scroll(function () {
        totop.toggle($(this).scrollTop() > 100);
    });

    totop.on("click", function () {
        $("body, html").animate({ scrollTop: 0 }, 500);
        return false;
    });
});
