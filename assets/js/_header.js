$(function () {
    /*********************************************
      * ヘッダー高さとボディの高さを計算し、CSS変数に格納
      * var(--header-height)
      * var(--body-height)
      ********************************************/
    function setHeaderHeight() {
        const $header = $("#js-header");
        if ($header.length) {
            const headerHeight = $header.outerHeight() + "px";
            $(":root").css("--header-height", headerHeight);
            $("body").addClass("js-header-ready");
        }
    }

    function setBodyHeight() {
        const bodyHeight = document.documentElement.scrollHeight + "px";
        $(":root").css("--body-height", bodyHeight);
        $("body").addClass("js-body-ready");
    }

    setHeaderHeight();
    setBodyHeight();

    $(window).on("resize load", function () {
        setHeaderHeight();
        setBodyHeight();
    });

    /*********************************************
     * ヘッダースクロールアニメーション
     ********************************************/
    const $header = $(".l-header");

    $(window).on("load scroll", function () {
        const windowHeight = $(window).height();
        const winWidth = $(window).width();
        const winWidthSm = 941;
        const topWindow = $(window).scrollTop();

        $(".js-anime").each(function () {
            const self = $(this);
            const targetPosition = self.offset().top;
            const triggerPosition =
                winWidth > winWidthSm
                    ? targetPosition - windowHeight + windowHeight / 5
                    : targetPosition - windowHeight + windowHeight / 10;

            if (topWindow > triggerPosition) {
                self.addClass("scrolled");
            }
        });
    });

    /*********************************************
     * ヘッダー表示制御
     ********************************************/
    let lastScrollTop = 0;
    let isHeaderHidden = false;
    let headerHeight = $header.outerHeight();
    let isLargeScreen = $(window).width() >= 992;
    const scrollThreshold = 1;
    const scrollThresholdHide = 400;

    function handleScroll() {
        if (!isLargeScreen) return;

        const scrollTop = $(window).scrollTop();
        $header.toggleClass("on", scrollTop > scrollThreshold);

        const isScrollingDown = scrollTop > lastScrollTop;

        if (scrollTop > scrollThresholdHide && isScrollingDown && !isHeaderHidden) {
            $header.animate({ top: -headerHeight }, 10);
            isHeaderHidden = true;
        } else if (
            (!isScrollingDown || scrollTop <= scrollThresholdHide) &&
            isHeaderHidden
        ) {
            $header.animate({ top: 0 }, 10);
            isHeaderHidden = false;
        }

        lastScrollTop = scrollTop;
    }

    function handleResize() {
        isLargeScreen = $(window).width() >= 992;
        headerHeight = $header.outerHeight();
        if (!isLargeScreen) {
            $header.css("top", "");
            isHeaderHidden = false;
        }
    }

    $(window).on("scroll", handleScroll);
    handleResize();

});
