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
     * スクロールアニメーション
     ********************************************/
    const $header = $(".l-header");

    const animeTargets = document.querySelectorAll(".js-anime");
    if (animeTargets.length) {
        const winWidthSm = 941;
        // PCは画面下から1/5、SPは1/10の位置で発火
        const bottomMargin = $(window).width() > winWidthSm ? "-20%" : "-10%";

        const animeObserver = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                // 表示領域に入った、または既に通過済みの要素にクラスを付与
                if (entry.isIntersecting || entry.boundingClientRect.top < 0) {
                    entry.target.classList.add("scrolled");
                    obs.unobserve(entry.target);
                }
            });
        }, { rootMargin: "0px 0px " + bottomMargin + " 0px" });

        animeTargets.forEach(function (el) {
            animeObserver.observe(el);
        });
    }

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

    /*********************************************
     * 子メニュー展開
     ********************************************/
    document.querySelectorAll('.js-gnaviToggle').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const item = toggle.closest('.js-gnaviItem');
            const isOpen = item.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(isOpen));
        });
    });
});
