jQuery(function ($) {

  /**
   * ヘッダー高さ
   */
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

  /**
   * スクロールアニメーション
   */
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

  /**
   * ヘッダー表示制御
   */
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
    } else if ((!isScrollingDown || scrollTop <= scrollThresholdHide) && isHeaderHidden) {
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

  /**
   * ToTop
   */
  const totop = $("#js-totop").hide();

  $(window).scroll(function () {
    totop.toggle($(this).scrollTop() > 100);
  });

  totop.on("click", function () {
    $("body, html").animate({ scrollTop: 0 }, 500);
    return false;
  });

  /**
   * slick
   */
  if ($(".js-mvSlide").length) {
    $(".js-mvSlide").slick({
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

  /**
   * 多言語翻訳
   */
  if ($("#google_translate_element").length) {
    function googleTranslateElementInit() {
      try {
        new google.translate.TranslateElement({
          pageLanguage: 'ja',
          includedLanguages: 'ja,en,zh-CN,ko',
          layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL
        }, 'google_translate_element');
      } catch (e) {
        console.error("Google翻訳初期化エラー:", e);
      }
    }

    function loadTranslateScript() {
      const old = document.querySelector('script[src*="translate_a/element.js"]');
      if (old) old.remove();

      const script = document.createElement('script');
      script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit&ts=' + Date.now();
      script.defer = true;
      document.head.appendChild(script);
    }

    function ensureWidget() {
      const widget = document.querySelector('#google_translate_element .goog-te-combo');
      if (!widget) {
        googleTranslateElementInit();
      }
    }

    function clearGoogTransCookie() {
      document.cookie.split(";").forEach(function (c) {
        if (c.trim().startsWith("googtrans=")) {
          document.cookie = c.split("=")[0] +
            "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
        }
      });
    }

    clearGoogTransCookie();
    loadTranslateScript();

    let retry = 0;
    const checkInterval = setInterval(() => {
      if (document.querySelector('#google_translate_element .goog-te-combo')) {
        clearInterval(checkInterval);
      } else if (retry > 5) {
        console.error("ウィジェットの初期化に失敗 → ボタン表示へ切替");
        clearInterval(checkInterval);
        document.getElementById('google_translate_element').innerHTML =
          '<button onclick="manualRetry()" style="background:orange;padding:10px;">翻訳ウィジェット再読み込み</button>';
      } else {
        ensureWidget();
      }
      retry++;
    }, 2000);

    function manualRetry() {
      document.getElementById('google_translate_element').innerHTML = '';
      loadTranslateScript();
    }

    jQuery(function ($) {
      function applySavedLang() {
        const savedLang = localStorage.getItem('googtransLang');
        const selectBox = document.querySelector('.goog-te-combo');

        if (savedLang && selectBox) {
          selectBox.value = savedLang === 'ja' ? '' : savedLang;
          selectBox.dispatchEvent(new Event('change'));
          return true;
        }
        return false;
      }

      let retryCount = 0;
      const interval = setInterval(() => {
        if (applySavedLang() || retryCount > 5) {
          clearInterval(interval);
        }
        retryCount++;
      }, 1000);


      $(document).on('click', '.js-lang-btn', function (e) {
        e.preventDefault();

        const selectBox = document.querySelector('.goog-te-combo');
        if (!selectBox) {
          alert('翻訳ウィジェットがまだ初期化されていません');
          return;
        }

        let targetLang = 'ja';
        if ($(this).hasClass('js-lang-en')) targetLang = 'en';
        else if ($(this).hasClass('js-lang-ko')) targetLang = 'ko';
        else if ($(this).hasClass('js-lang-zh')) targetLang = 'zh-CN';

        selectBox.value = targetLang;
        selectBox.dispatchEvent(new Event('change'));

        if (targetLang === 'ja') {
          localStorage.removeItem('googtransLang');
        } else {
          localStorage.setItem('googtransLang', targetLang);
        }

        $('.js-lang-btn').removeClass('is-active');
        $(this).addClass('is-active');

        location.reload();
      });


    });

    window.manualRetry = manualRetry;
  }

  /**
   *
   */
  if ($('.item-image').length) {
    const $modal = $("#imgModal");
    const $img = $("#imgModal img");
    const scale = 2.5

    let isDragging = false;
    let startX, startY, moveX = 0,
      moveY = 0;

    // モーダルを開く
    $(".item-image").on("click", function () {
      const src = $(this).find("img").attr("src");
      resetModal();
      $img.attr("src", src);
      $modal.fadeIn();
    });

    // モーダルをリセット
    function resetModal() {
      $img.removeClass("zoomed dragging");
      $img.css("transform", "translate(-50%, -50%)");
      moveX = moveY = 0;
    }

    // 画像クリックで拡大/縮小
    $img.on("click", function (e) {
      if (isDragging) return;

      if (!$img.hasClass("zoomed")) {
        // 拡大
        $img.addClass("zoomed").css(
          "transform",
          `translate(calc(-50% + ${moveX}px), calc(-50% + ${moveY}px)) scale(${scale})`
        );
      } else {
        // 縮小
        resetModal();
      }
    });

    // ドラッグ開始
    $img.on("mousedown", function (e) {
      if (!$img.hasClass("zoomed")) return;

      isDragging = true;
      $img.addClass("dragging");
      startX = e.pageX - moveX;
      startY = e.pageY - moveY;
      e.preventDefault();
    });

    // ドラッグ移動
    $(document).on("mousemove", function (e) {
      if (!isDragging) return;

      moveX = e.pageX - startX;
      moveY = e.pageY - startY;
      $img.css(
        "transform",
        `translate(calc(-50% + ${moveX}px), calc(-50% + ${moveY}px)) scale(${scale})`
      );
    });

    // ドラッグ終了
    $(document).on("mouseup", function () {
      if (isDragging) {
        isDragging = false;
        $img.removeClass("dragging");
      }
    });

    // 背景クリックで閉じる
    $modal.on("click", function (e) {
      if (e.target.id === "imgModal") {
        $modal.fadeOut();
      }
    });

    // ESCキーで閉じる
    $(document).on("keydown", function (e) {
      if (e.key === "Escape" && $modal.is(":visible")) {
        $modal.fadeOut();
      }
    });
  }

});