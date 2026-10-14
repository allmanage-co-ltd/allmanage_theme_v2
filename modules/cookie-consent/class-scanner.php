<?php

/**
 * Scanner — 既知 Cookie の定義リストと管理画面ページの提供
 *
 * - 既知 Cookie を静的定義として保持し、カテゴリ・用途・期限を管理画面に表示する
 * - ブラウザで実際に発行されている Cookie は JS 側で取得して照合する
 * - 管理メニュー直下（トップレベル）にページを追加する
 */

namespace CookieConsent;

if (!defined('ABSPATH')) {
    exit;
}

class Scanner
{
    /** メニュースラッグ */
    const MENU_SLUG = 'cookie-scanner';

    /** 既知 Cookie の定義リスト */
    const KNOWN_COOKIES = [
        // ── 必須 ──────────────────────────────────────────────
        'wordpress_logged_in_*' => [
            'category'    => 'necessary',
            'service'     => 'WordPress',
            'description' => 'ログイン状態の維持に使用します。',
            'expiry'      => 'セッション / 14日',
            'domain'      => '自ドメイン',
        ],
        'wordpress_sec_*' => [
            'category'    => 'necessary',
            'service'     => 'WordPress',
            'description' => '管理画面のセキュリティトークンです。',
            'expiry'      => 'セッション',
            'domain'      => '自ドメイン',
        ],
        'wp-settings-*' => [
            'category'    => 'necessary',
            'service'     => 'WordPress',
            'description' => '管理画面の表示設定（wp-settings-1）と最終設定時刻（wp-settings-time-1）を保存します。',
            'expiry'      => '1年',
            'domain'      => '自ドメイン',
        ],
        'mw-wp-form-token' => [
            'category'    => 'necessary',
            'service'     => 'MW WP Form',
            'description' => 'フォームの二重送信防止・CSRF対策に使用するトークンです。',
            'expiry'      => 'セッション',
            'domain'      => '自ドメイン',
        ],
        'mw-wp-form_session_*' => [
            'category'    => 'necessary',
            'service'     => 'MW WP Form',
            'description' => 'フォームの入力内容をセッション間で保持するために使用します（フォームIDごとに発行）。',
            'expiry'      => 'セッション',
            'domain'      => '自ドメイン',
        ],
        'wordpress_test_cookie' => [
            'category'    => 'necessary',
            'service'     => 'WordPress',
            'description' => 'ブラウザが Cookie に対応しているかを確認するためのテスト用 Cookie です。',
            'expiry'      => 'セッション',
            'domain'      => '自ドメイン',
        ],
        'wp_lang' => [
            'category'    => 'necessary',
            'service'     => 'WordPress',
            'description' => 'ログイン画面・管理画面の表示言語を保持します。',
            'expiry'      => 'セッション',
            'domain'      => '自ドメイン',
        ],
        'PHPSESSID' => [
            'category'    => 'necessary',
            'service'     => 'PHP',
            'description' => 'セッション管理に使用されます。',
            'expiry'      => 'セッション',
            'domain'      => '自ドメイン',
        ],
        'cookie_consent' => [
            'category'    => 'necessary',
            'service'     => 'Cookie同意管理',
            'description' => 'ユーザーの Cookie 同意設定を保存します。このサイト独自の Cookie です。',
            'expiry'      => '1年',
            'domain'      => '自ドメイン',
        ],

        // ── パフォーマンス ────────────────────────────────────────
        '_ga' => [
            'category'    => 'performance',
            'service'     => 'Google Analytics 4',
            'description' => 'ユーザーを識別するための主要な識別子です。訪問者を区別するために使用します。',
            'expiry'      => '2年',
            'domain'      => '.example.com',
        ],
        '_ga_*' => [
            'category'    => 'performance',
            'service'     => 'Google Analytics 4',
            'description' => 'セッション状態を維持するために使用します（測定 ID ごとに作成）。',
            'expiry'      => '2年',
            'domain'      => '.example.com',
        ],
        '_gid' => [
            'category'    => 'performance',
            'service'     => 'Google Analytics（UA）',
            'description' => '24時間以内のユーザーを識別します。',
            'expiry'      => '24時間',
            'domain'      => '.example.com',
        ],
        '_gat' => [
            'category'    => 'performance',
            'service'     => 'Google Analytics（UA）',
            'description' => 'リクエスト数を制御するために使用します。',
            'expiry'      => '1分',
            'domain'      => '.example.com',
        ],
        '_clck' => [
            'category'    => 'performance',
            'service'     => 'Microsoft Clarity',
            'description' => 'ユーザーの Clarity セッションを識別します。',
            'expiry'      => '1年',
            'domain'      => '.example.com',
        ],
        '_clsk' => [
            'category'    => 'performance',
            'service'     => 'Microsoft Clarity',
            'description' => '複数のページビューを 1 つのセッションにまとめます。',
            'expiry'      => '1日',
            'domain'      => '.example.com',
        ],
        'CLID' => [
            'category'    => 'performance',
            'service'     => 'Microsoft Clarity',
            'description' => 'Clarity がユーザーを最初に確認した日時を記録します。',
            'expiry'      => '1年',
            'domain'      => '.clarity.ms',
        ],

        // ── 機能 ──────────────────────────────────────────────
        'YSC' => [
            'category'    => 'functional',
            'service'     => 'YouTube',
            'description' => '埋め込み YouTube 動画の視聴セッションを追跡します。',
            'expiry'      => 'セッション',
            'domain'      => '.youtube.com',
        ],
        'VISITOR_INFO1_LIVE' => [
            'category'    => 'functional',
            'service'     => 'YouTube',
            'description' => '帯域幅を推定するために YouTube が設定します。',
            'expiry'      => '6ヶ月',
            'domain'      => '.youtube.com',
        ],
        'VISITOR_PRIVACY_METADATA' => [
            'category'    => 'functional',
            'service'     => 'YouTube',
            'description' => 'YouTube のプライバシー設定を保存します。',
            'expiry'      => '6ヶ月',
            'domain'      => '.youtube.com',
        ],
        'GPS' => [
            'category'    => 'functional',
            'service'     => 'YouTube',
            'description' => 'モバイルデバイスの GPS 位置情報を登録します。',
            'expiry'      => '30分',
            'domain'      => '.youtube.com',
        ],
        'NID' => [
            'category'    => 'functional',
            'service'     => 'Google（Maps 等）',
            'description' => 'Google サービスでのユーザー設定（言語・検索結果数等）を保存します。',
            'expiry'      => '6ヶ月',
            'domain'      => '.google.com',
        ],

        // ── 広告 ──────────────────────────────────────────────
        '_fbp' => [
            'category'    => 'marketing',
            'service'     => 'Meta（Facebook）Pixel',
            'description' => 'Facebook 広告のターゲティングと計測に使用します。',
            'expiry'      => '3ヶ月',
            'domain'      => '.example.com',
        ],
        '_fbc' => [
            'category'    => 'marketing',
            'service'     => 'Meta（Facebook）Pixel',
            'description' => 'Facebook 広告のクリック識別子を保存します。',
            'expiry'      => '2年',
            'domain'      => '.example.com',
        ],
        '_gcl_au' => [
            'category'    => 'marketing',
            'service'     => 'Google Ads',
            'description' => 'Google Ads のコンバージョン計測に使用します。',
            'expiry'      => '3ヶ月',
            'domain'      => '.example.com',
        ],
        '_gcl_aw' => [
            'category'    => 'marketing',
            'service'     => 'Google Ads',
            'description' => 'Google 検索広告のクリック情報を保存します。',
            'expiry'      => '90日',
            'domain'      => '.example.com',
        ],
        'IDE' => [
            'category'    => 'marketing',
            'service'     => 'Google DoubleClick',
            'description' => 'リターゲティング広告の表示に使用します。',
            'expiry'      => '1年',
            'domain'      => '.doubleclick.net',
        ],
        'fr' => [
            'category'    => 'marketing',
            'service'     => 'Meta（Facebook）',
            'description' => 'ターゲット広告の配信に使用します。',
            'expiry'      => '3ヶ月',
            'domain'      => '.facebook.com',
        ],
    ];

    /**
     * カテゴリ定義（ラベル・色）
     */
    const CATEGORIES = [
        'necessary'   => ['label' => '必須',             'color' => '#198754'],
        'functional'  => ['label' => '機能',             'color' => '#0d6efd'],
        'performance' => ['label' => '分析',             'color' => '#fd7e14'],
        'marketing'   => ['label' => '広告',             'color' => '#dc3545'],
        'unknown'     => ['label' => '不明',             'color' => '#6c757d'],
    ];

    public function boot(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_ajax_cc_scan_cookies', [$this, 'ajaxScan']);
    }

    /**
     * 管理メニュー直下にページを追加
     */
    public function addMenu(): void
    {
        add_menu_page(
            'Cookie 一覧',
            'Cookie 一覧',
            'manage_options',
            self::MENU_SLUG,
            [$this, 'render'],
            'dashicons-shield',
            81
        );
    }

    /**
     * アセット登録
     */
    public function enqueue(string $hook): void
    {
        if ($hook !== 'toplevel_page_' . self::MENU_SLUG) {
            return;
        }

        // 既知Cookieの定義を JS に渡す
        wp_add_inline_script(
            'jquery',
            'window.CookieScannerData = ' . wp_json_encode([
                'knownCookies' => self::KNOWN_COOKIES,
                'categories'   => self::CATEGORIES,
                'ajaxUrl'      => admin_url('admin-ajax.php'),
                'nonce'        => wp_create_nonce('cc_scan_cookies'),
            ]) . ';',
            'before'
        );
    }

    /**
     * 管理画面ページを描画
     */
    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $categories  = self::CATEGORIES;
        $known       = self::KNOWN_COOKIES;
        $site_url    = get_site_url();
        ?>
        <div class="wrap cc-scanner">
            <h1 class="cc-scanner__title">
                <span class="dashicons dashicons-shield" style="font-size:28px;margin-right:8px;vertical-align:middle;color:#0d6efd;"></span>
                Cookie 一覧
            </h1>
            <p class="cc-scanner__lead">
                このサイトで使用している Cookie の一覧です。お客様への説明資料としてご活用ください。<br>
                <strong>「現在のブラウザをスキャン」</strong> を実行すると、実際に発行されている Cookie を検出して照合します。
            </p>

            <div class="cc-scanner__actions">
                <button type="button" id="cc-do-scan" class="button button-primary">
                    現在のブラウザをスキャン
                </button>
                <a href="<?php echo esc_url($site_url); ?>" target="_blank" class="button">
                    サイトを開く（Cookie を発行させてからスキャン）
                </a>
                <span id="cc-scan-status" class="cc-scan-status"></span>
            </div>

            <!-- カテゴリタブ -->
            <div class="cc-scanner__tabs" id="cc-tabs">
                <button type="button" class="cc-tab cc-tab--active" data-tab="all">すべて</button>
                <?php foreach ($categories as $key => $cat) : ?>
                <button type="button" class="cc-tab" data-tab="<?php echo esc_attr($key); ?>">
                    <span class="cc-badge" style="background:<?php echo esc_attr($cat['color']); ?>"><?php echo esc_html($cat['label']); ?></span>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- 既知 Cookie テーブル -->
            <div class="cc-scanner__section">
                <h2 class="cc-scanner__section-title">既知の Cookie 定義</h2>
                <table class="wp-list-table widefat fixed striped cc-table" id="cc-known-table">
                    <thead>
                        <tr>
                            <th style="width:180px">Cookie 名</th>
                            <th style="width:100px">カテゴリ</th>
                            <th style="width:160px">サービス</th>
                            <th>用途・説明</th>
                            <th style="width:100px">有効期限</th>
                            <th style="width:80px">発行元</th>
                            <th style="width:80px">検出</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($known as $name => $cookie) :
                            $cat = $categories[$cookie['category']] ?? $categories['unknown'];
                        ?>
                        <tr data-category="<?php echo esc_attr($cookie['category']); ?>" class="cc-row">
                            <td><code class="cc-cookie-name"><?php echo esc_html($name); ?></code></td>
                            <td>
                                <span class="cc-badge" style="background:<?php echo esc_attr($cat['color']); ?>">
                                    <?php echo esc_html($cat['label']); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($cookie['service']); ?></td>
                            <td><?php echo esc_html($cookie['description']); ?></td>
                            <td><?php echo esc_html($cookie['expiry']); ?></td>
                            <td><small><?php echo esc_html($cookie['domain']); ?></small></td>
                            <td class="cc-detected" data-name="<?php echo esc_attr($name); ?>">
                                <span class="cc-detect-icon cc-detect--unknown" title="未スキャン">―</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- スキャンで見つかった未知の Cookie -->
            <div class="cc-scanner__section" id="cc-unknown-section" style="display:none">
                <h2 class="cc-scanner__section-title">
                    スキャンで検出された未登録の Cookie
                    <small style="font-weight:normal;font-size:13px;margin-left:8px;color:#6c757d">定義リストにない Cookie です。確認が必要な場合があります。</small>
                </h2>
                <table class="wp-list-table widefat fixed striped cc-table">
                    <thead>
                        <tr>
                            <th style="width:200px">Cookie 名</th>
                            <th style="width:100px">カテゴリ</th>
                            <th>値（先頭50文字）</th>
                        </tr>
                    </thead>
                    <tbody id="cc-unknown-tbody"></tbody>
                </table>
            </div>
        </div>

        <style>
        .cc-scanner { max-width: 1200px; }
        .cc-scanner__title { display: flex; align-items: center; margin-bottom: 8px; }
        .cc-scanner__lead { color: #3c434a; margin-bottom: 20px; line-height: 1.7; }
        .cc-scanner__actions { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
        .cc-scan-status { font-size: 13px; color: #646970; }
        .cc-scanner__tabs { display: flex; gap: 4px; margin-bottom: 16px; border-bottom: 2px solid #dcdcde; padding-bottom: 0; }
        .cc-tab { background: none; border: none; border-bottom: 3px solid transparent; padding: 8px 14px; cursor: pointer; font-size: 13px; font-weight: 600; color: #646970; margin-bottom: -2px; transition: color 0.15s, border-color 0.15s; }
        .cc-tab:hover { color: #0d6efd; }
        .cc-tab--active { color: #0d6efd; border-bottom-color: #0d6efd; }
        .cc-scanner__section { margin-bottom: 32px; }
        .cc-scanner__section-title { font-size: 15px; font-weight: 700; margin: 0 0 12px; color: #1d2327; }
        .cc-table code.cc-cookie-name { font-size: 12px; background: #f0f0f1; padding: 2px 6px; border-radius: 3px; white-space: nowrap; }
        .cc-badge { display: inline-block; font-size: 11px; font-weight: 700; color: #fff; padding: 2px 8px; border-radius: 20px; white-space: nowrap; }
        .cc-detect-icon { font-size: 16px; }
        .cc-detect--found { color: #198754; }
        .cc-detect--found::before { content: '✓'; }
        .cc-detect--notfound { color: #adb5bd; }
        .cc-detect--notfound::before { content: '―'; }
        .cc-detect--unknown { color: #adb5bd; }
        .cc-row[style*="display:none"] { display: none !important; }
        </style>

        <script>
        (function ($) {
            const data = window.CookieScannerData || {};
            const known = data.knownCookies || {};
            const categories = data.categories || {};

            // ── タブ切り替え ──────────────────────────────────
            $('#cc-tabs').on('click', '.cc-tab', function () {
                const tab = $(this).data('tab');
                $('.cc-tab').removeClass('cc-tab--active');
                $(this).addClass('cc-tab--active');

                if (tab === 'all') {
                    $('#cc-known-table .cc-row').show();
                } else {
                    $('#cc-known-table .cc-row').each(function () {
                        $(this).toggle($(this).data('category') === tab);
                    });
                }
            });

            // ── スキャン実行 ──────────────────────────────────
            $('#cc-do-scan').on('click', function () {
                const $btn = $(this);
                const $status = $('#cc-scan-status');

                $btn.prop('disabled', true);
                $status.text('スキャン中...');

                // document.cookie から全 Cookie を取得
                const browserCookies = {};
                document.cookie.split(';').forEach(function (pair) {
                    const idx = pair.indexOf('=');
                    if (idx < 0) return;
                    const name = pair.slice(0, idx).trim();
                    const val  = pair.slice(idx + 1).trim();
                    if (name) browserCookies[name] = val;
                });

                // 既知 Cookie との照合（ワイルドカード対応）
                // ※ * を .* に変換する前にリテラルの . をエスケープする順序が重要
                function patternToRegex(pattern) {
                    return new RegExp(
                        '^' + pattern.replace(/\./g, '\\.').replace(/\*/g, '.*') + '$'
                    );
                }

                const foundNames = new Set();
                Object.keys(browserCookies).forEach(function (cookieName) {
                    Object.keys(known).forEach(function (pattern) {
                        if (patternToRegex(pattern).test(cookieName)) {
                            foundNames.add(pattern);
                        }
                    });
                });

                // 検出結果を各行に反映
                $('#cc-known-table .cc-detected').each(function () {
                    const name = $(this).data('name');
                    const found = foundNames.has(name);
                    $(this).html(
                        found
                            ? '<span class="cc-detect-icon cc-detect--found" title="検出されました">✓</span>'
                            : '<span class="cc-detect-icon cc-detect--notfound" title="現在のブラウザでは未検出">―</span>'
                    );
                });

                // 未登録 Cookie を検出
                const unknownCookies = [];
                Object.entries(browserCookies).forEach(function ([name, val]) {
                    const isKnown = Object.keys(known).some(function (pattern) {
                        return patternToRegex(pattern).test(name);
                    });
                    if (!isKnown) {
                        unknownCookies.push({ name, val });
                    }
                });

                const $unknownSection = $('#cc-unknown-section');
                const $tbody = $('#cc-unknown-tbody').empty();

                if (unknownCookies.length > 0) {
                    unknownCookies.forEach(function ({ name, val }) {
                        $tbody.append(
                            '<tr>' +
                            '<td><code class="cc-cookie-name">' + escHtml(name) + '</code></td>' +
                            '<td><span class="cc-badge" style="background:#6c757d">不明</span></td>' +
                            '<td><small>' + escHtml(val.slice(0, 50)) + (val.length > 50 ? '…' : '') + '</small></td>' +
                            '</tr>'
                        );
                    });
                    $unknownSection.show();
                } else {
                    $unknownSection.hide();
                }

                const total = Object.keys(browserCookies).length;
                const found = foundNames.size;
                $status.text('スキャン完了 — ' + total + ' 件の Cookie を検出、うち ' + found + ' 件が既知のリストと一致しました');
                $btn.prop('disabled', false);
            });

            function escHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

        })(jQuery);
        </script>
        <?php
    }

    /**
     * Ajax ハンドラ（将来の拡張用）
     */
    public function ajaxScan(): void
    {
        check_ajax_referer('cc_scan_cookies', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Forbidden', 403);
        }

        wp_send_json_success([]);
    }
}
