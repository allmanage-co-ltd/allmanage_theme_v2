/*********************************************
 * 多言語翻訳
 ********************************************/
if ($("#google_translate_element").length) {
    function googleTranslateElementInit() {
        try {
            new google.translate.TranslateElement(
                {
                    pageLanguage: "ja",
                    includedLanguages: "ja,en,zh-CN,ko",
                    layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL,
                },
                "google_translate_element"
            );
        } catch (e) {
            console.error("Google翻訳初期化エラー:", e);
        }
    }
    // コールバック（cb=）から呼べるようグローバルに公開
    window.googleTranslateElementInit = googleTranslateElementInit;

    function loadTranslateScript() {
        const old = document.querySelector(
            'script[src*="translate_a/element.js"]'
        );
        if (old) old.remove();

        // キャッシュを効かせるためタイムスタンプは付与しない
        const script = document.createElement("script");
        script.src =
            "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
        script.async = true;
        document.head.appendChild(script);
    }

    function ensureWidget() {
        const widget = document.querySelector(
            "#google_translate_element .goog-te-combo"
        );
        // スクリプト読み込み前は初期化しない
        if (!widget && window.google && google.translate) {
            googleTranslateElementInit();
        }
    }

    function clearGoogTransCookie() {
        document.cookie.split(";").forEach(function (c) {
            if (c.trim().startsWith("googtrans=")) {
                document.cookie =
                    c.split("=")[0] + "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
            }
        });
    }

    function applySavedLang() {
        const savedLang = localStorage.getItem("googtransLang");
        const selectBox = document.querySelector(".goog-te-combo");

        if (savedLang && selectBox) {
            selectBox.value = savedLang === "ja" ? "" : savedLang;
            selectBox.dispatchEvent(new Event("change"));
            return true;
        }
        return false;
    }

    function manualRetry() {
        document.getElementById("google_translate_element").innerHTML = "";
        loadTranslateScript();
    }

    function startTranslate() {
        loadTranslateScript();

        let retry = 0;
        const checkInterval = setInterval(() => {
            if (document.querySelector("#google_translate_element .goog-te-combo")) {
                clearInterval(checkInterval);

                // ウィジェット生成後に保存済みの言語を適用
                let retryCount = 0;
                const interval = setInterval(() => {
                    if (applySavedLang() || retryCount > 5) {
                        clearInterval(interval);
                    }
                    retryCount++;
                }, 1000);
            } else if (retry > 5) {
                console.error("ウィジェットの初期化に失敗 → ボタン表示へ切替");
                clearInterval(checkInterval);
                document.getElementById("google_translate_element").innerHTML =
                    '<button onclick="manualRetry()" style="background:orange;padding:10px;">翻訳ウィジェット再読み込み</button>';
            } else {
                ensureWidget();
            }
            retry++;
        }, 2000);
    }

    clearGoogTransCookie();

    // ページの読み込み完了後、ブラウザの空き時間に読み込む（load を遅らせない）
    function scheduleTranslate() {
        if ("requestIdleCallback" in window) {
            requestIdleCallback(startTranslate, { timeout: 2000 });
        } else {
            setTimeout(startTranslate, 200);
        }
    }

    if (document.readyState === "complete") {
        scheduleTranslate();
    } else {
        window.addEventListener("load", scheduleTranslate, { once: true });
    }

    jQuery(function ($) {
        $(document).on("click", ".js-lang-btn", function (e) {
            e.preventDefault();

            const selectBox = document.querySelector(".goog-te-combo");
            if (!selectBox) {
                alert("翻訳ウィジェットがまだ初期化されていません");
                return;
            }

            let targetLang = "ja";
            if ($(this).hasClass("js-lang-en")) targetLang = "en";
            else if ($(this).hasClass("js-lang-ko")) targetLang = "ko";
            else if ($(this).hasClass("js-lang-zh")) targetLang = "zh-CN";

            selectBox.value = targetLang;
            selectBox.dispatchEvent(new Event("change"));

            if (targetLang === "ja") {
                localStorage.removeItem("googtransLang");
            } else {
                localStorage.setItem("googtransLang", targetLang);
            }

            $(".js-lang-btn").removeClass("is-active");
            $(this).addClass("is-active");

            location.reload();
        });
    });

    window.manualRetry = manualRetry;
}
