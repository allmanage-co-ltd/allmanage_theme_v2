/*********************************************
 * 多言語翻訳
********************************************/
$(function () {
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

        function loadTranslateScript() {
            const old = document.querySelector(
                'script[src*="translate_a/element.js"]'
            );
            if (old) old.remove();

            const script = document.createElement("script");
            script.src =
                "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit&ts=" +
                Date.now();
            script.defer = true;
            document.head.appendChild(script);
        }

        function ensureWidget() {
            const widget = document.querySelector(
                "#google_translate_element .goog-te-combo"
            );
            if (!widget) {
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

        clearGoogTransCookie();
        loadTranslateScript();

        let retry = 0;
        const checkInterval = setInterval(() => {
            if (document.querySelector("#google_translate_element .goog-te-combo")) {
                clearInterval(checkInterval);
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

        function manualRetry() {
            document.getElementById("google_translate_element").innerHTML = "";
            loadTranslateScript();
        }

        jQuery(function ($) {
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

            let retryCount = 0;
            const interval = setInterval(() => {
                if (applySavedLang() || retryCount > 5) {
                    clearInterval(interval);
                }
                retryCount++;
            }, 1000);

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
});
