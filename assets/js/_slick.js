/*********************************************
 * slick
 ********************************************/
$(function () {
    const jsMvSlide = $(".js-mvSlide");
    if (jsMvSlide.length) {
        jsMvSlide.slick({
            fade: true,
            autoplay: true,
            autoplaySpeed: 6000,
            speed: 1200,
            arrows: false,
            dots: true,
            pauseOnFocus: false,
            pauseOnHover: false,
        });
    }
});
