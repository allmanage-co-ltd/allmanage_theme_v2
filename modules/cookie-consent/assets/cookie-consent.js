/**
 * cookie-consent.js — Cookie 同意 UI の制御
 *
 * ・初回訪問時にバナーを表示
 * ・詳細設定モーダルでカテゴリ別トグル
 * ・保存時に Cookie を書き込んでページをリロード
 * ・functional 非同意時は対象 iframe の src をブロック
 */

document.addEventListener('DOMContentLoaded', function () {
    (function () {
        'use strict';

        const config = window.CookieConsentConfig || {};

        // ─────────────────────────────────────────────────────────────
        // Cookie 操作
        // ─────────────────────────────────────────────────────────────

        /**
         * 同意 Cookie を保存してリロード
         *
         * @param {{ necessary: boolean, functional: boolean, performance: boolean, marketing: boolean }} consent
         */
        function saveConsent(consent) {
            const value = encodeURIComponent(JSON.stringify(consent));
            document.cookie =
                config.cookieName + '=' + value +
                '; path=/' +
                '; max-age=' + config.cookieExpire +
                '; SameSite=Lax';

            // 拒否されたカテゴリの既存 Cookie を削除
            if (!consent.performance) {
                clearAnalyticsCookies();
            }

            // PHP 側でタグ出力を制御するためリロード
            location.reload();
        }

        /**
         * 分析系の既存 Cookie を削除（GA4 の _ga / _ga_* 等）
         */
        function clearAnalyticsCookies() {
            const domain = '.' + location.hostname.replace(/^www\./, '');
            const ga_pattern = /^_ga/;

            document.cookie.split(';').forEach(function (cookie) {
                const name = cookie.split('=')[0].trim();
                if (ga_pattern.test(name)) {
                    // ルートパス + ドメインなし
                    document.cookie = name + '=; path=/; max-age=0';
                    // ドメイン付き（.example.com）
                    document.cookie = name + '=; path=/; max-age=0; domain=' + domain;
                }
            });
        }

        // ─────────────────────────────────────────────────────────────
        // iframe ブロック（functional 非同意時）
        // ─────────────────────────────────────────────────────────────

        /**
         * functional 非同意の場合、対象ホストの iframe を src='' に差し替えてブロック
         * プレースホルダーを挿入して UX を維持する
         */
        function blockFunctionalIframes() {
            if (config.consent && config.consent.functional) {
                return;
            }

            const hosts = config.functionalHosts || [];

            document.querySelectorAll('iframe').forEach(function (iframe) {
                const src = iframe.getAttribute('src') || '';
                const dataSrc = iframe.getAttribute('data-cookie-src') || '';
                const target = src || dataSrc;

                const isBlocked = hosts.some(function (host) {
                    return target.indexOf(host) !== -1;
                });

                if (!isBlocked || !target) {
                    return;
                }

                // src を退避してブロック
                iframe.setAttribute('data-cookie-src', target);
                iframe.removeAttribute('src');

                // プレースホルダーを挿入
                const placeholder = document.createElement('div');
                placeholder.className = 'cc-iframe-placeholder';
                placeholder.innerHTML =
                    '<p>このコンテンツを表示するには「機能性クッキー」の許可が必要です。</p>' +
                    '<button type="button" class="cc-btn cc-btn--accept cc-iframe-enable">クッキー を許可して表示</button>';

                placeholder.querySelector('.cc-iframe-enable').addEventListener('click', function () {
                    // functional だけ許可して保存
                    const current = config.consent || {};
                    saveConsent({
                        necessary:   true,
                        functional:  true,
                        performance: current.performance || false,
                        marketing:   current.marketing   || false,
                    });
                });

                iframe.parentNode.insertBefore(placeholder, iframe);
                iframe.style.display = 'none';
            });
        }

        // ─────────────────────────────────────────────────────────────
        // バナー / モーダル制御
        // ─────────────────────────────────────────────────────────────

        const banner = document.getElementById('cookie-consent-banner');
        const modal = document.getElementById('cookie-consent-modal');

        if (!banner || !modal) {
            return;
        }

        /** バナーを表示 */
        function showBanner() {
            banner.hidden = false;
            banner.removeAttribute('aria-hidden');
        }

        /** バナーを非表示 */
        function hideBanner() {
            banner.hidden = true;
        }

        /** モーダルを開く */
        function openModal() {
            hideBanner();
            modal.hidden = false;
            modal.removeAttribute('aria-hidden');
            document.body.style.overflow = 'hidden';

            // 現在の同意状態をトグルに反映（有効カテゴリのみ）
            const consent = config.consent || {};
            (config.enabledCategories || []).forEach(function (key) {
                setToggle('cc-' + key, consent[key] || false);
            });

            // フォーカス管理
            const firstToggle = modal.querySelector('.cc-toggle__input');
            if (firstToggle) {
                firstToggle.focus();
            }
        }

        /** モーダルを閉じる */
        function closeModal() {
            modal.hidden = true;
            document.body.style.overflow = '';
            // バナーがまだなら再表示
            if (!config.exists) {
                showBanner();
            }
        }

        /** チェックボックスの状態をセット */
        function setToggle(id, value) {
            const el = document.getElementById(id);
            if (el) {
                el.checked = !!value;
            }
        }

        /** モーダルのトグル状態から同意オブジェクトを生成（有効カテゴリのみ収集） */
        function getModalConsent() {
            const consent = { necessary: true };
            // 全カテゴリをデフォルト false で初期化
            ['functional', 'performance', 'marketing'].forEach(function (key) {
                consent[key] = false;
            });
            // 有効カテゴリのチェック状態を反映
            (config.enabledCategories || []).forEach(function (key) {
                const el = document.getElementById('cc-' + key);
                consent[key] = !!(el && el.checked);
            });
            return consent;
        }

        // ─────────────────────────────────────────────────────────────
        // イベント登録
        // ─────────────────────────────────────────────────────────────

        // 「すべて許可」（バナー）
        document.getElementById('cc-accept-all').addEventListener('click', function () {
            saveConsent({ necessary: true, functional: true, performance: true, marketing: true });
        });

        // 「すべて拒否」（バナー）
        document.getElementById('cc-reject-all').addEventListener('click', function () {
            saveConsent({ necessary: true, functional: false, performance: false, marketing: false });
        });

        // 「詳細設定」（バナー）
        document.getElementById('cc-open-settings').addEventListener('click', openModal);

        // 「すべて許可」（モーダル）
        document.getElementById('cc-modal-accept').addEventListener('click', function () {
            saveConsent({ necessary: true, functional: true, performance: true, marketing: true });
        });

        // 「すべて拒否」（モーダル）
        document.getElementById('cc-modal-reject').addEventListener('click', function () {
            saveConsent({ necessary: true, functional: false, performance: false, marketing: false });
        });

        // 「選択を保存」（モーダル）
        document.getElementById('cc-modal-save').addEventListener('click', function () {
            saveConsent(getModalConsent());
        });

        // 左下の常時表示設定ボタン
        const trigger = document.getElementById('cc-settings-trigger');
        if (trigger) {
            trigger.addEventListener('click', openModal);
        }

        // オーバーレイクリックで閉じる
        document.getElementById('cc-modal-overlay').addEventListener('click', closeModal);

        // Esc キーで閉じる
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        // ─────────────────────────────────────────────────────────────
        // 初期化
        // ─────────────────────────────────────────────────────────────

        // functional 非同意の iframe をブロック
        blockFunctionalIframes();

        // Cookie 未設定（初回訪問）ならバナー表示
        if (!config.exists) {
            showBanner();
        }

    })();
});
