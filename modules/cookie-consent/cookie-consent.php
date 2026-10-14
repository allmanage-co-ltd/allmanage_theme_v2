<?php

/**
 * cookie-consent — GDPR 対応 Cookie 同意管理モジュール
 *
 * PHP 側で同意状態を判定し、許可されたタグだけ HTML に出力する。
 * 「ロードしてから止める」ではなく「許可されるまで存在しない」方式。
 *
 * PHP 7.4 以上 / WordPress 5.0 以上。外部依存なし。
 *
 * ═══════════════════════════════════════════════════════════════
 * 【導入手順】
 * ═══════════════════════════════════════════════════════════════
 *
 * 1. modules/index.php に 1 行追加する：
 *      require_once __DIR__ . '/cookie-consent/cookie-consent.php';
 *
 * 2. functions.php にサイト設定を追加する（下記カスタマイズ例を参照）
 *
 * ═══════════════════════════════════════════════════════════════
 * 【functions.php カスタマイズ例】
 * ═══════════════════════════════════════════════════════════════
 *
 *   // バナーに表示するサイト名
 *   add_filter('cookie_consent/site_name', fn() => 'サイト名');
 *
 *   // プライバシーポリシーページの URL
 *   add_filter('cookie_consent/privacy_url', fn() => '/privacypolicy/');
 *
 *   // サイトのメインカラー（許可ボタン・トグル ON・リンク色に適用）
 *   add_filter('cookie_consent/primary_color', fn() => '#1ab416');
 *
 *   // 表示するカテゴリを絞り込む（不要なものをコメントアウト）
 *   add_filter('cookie_consent/categories', function ($cats) {
 *       return array_intersect_key($cats, array_flip([
 *           'functional',   // 機能 Cookie（YouTube / Google Maps 等）
 *           'performance',  // パフォーマンス Cookie（GA4 / Clarity 等）
 *           // 'marketing', // 広告 Cookie（Meta Pixel 等）※使わなければコメントアウト
 *       ]));
 *   });
 *
 * ═══════════════════════════════════════════════════════════════
 * 【カテゴリ一覧】
 * ═══════════════════════════════════════════════════════════════
 *
 *   - necessary   : 必須（常に有効・拒否不可）
 *   - functional  : 機能 Cookie（YouTube / Google Maps 等の iframe）
 *   - performance : パフォーマンス Cookie（GA4 / Microsoft Clarity 等）
 *   - marketing   : 広告 Cookie（Meta Pixel / Google Ads 等）
 *
 * ═══════════════════════════════════════════════════════════════
 * 【仕組み】
 * ═══════════════════════════════════════════════════════════════
 *
 *   GA4 等: wp_head 出力をバッファリングし、非同意時に
 *           googletagmanager.com / clarity.ms を含む script タグを除去する。
 *           既存の出力元 PHP を触る必要はない。
 *
 *   iframe: functional 非同意時、YouTube / Google Maps 等の iframe src を
 *           退避してプレースホルダーに差し替える（JS 側で処理）。
 *
 * ═══════════════════════════════════════════════════════════════
 * 【応用フィルター】
 * ═══════════════════════════════════════════════════════════════
 *
 *   // iframe をブロックする外部ドメインを追加
 *   add_filter('cookie_consent/functional_hosts', function ($hosts) {
 *       $hosts[] = 'example.com';
 *       return $hosts;
 *   });
 *
 *   // analytics ブロック対象パターンを追加（正規表現）
 *   add_filter('cookie_consent/analytics_block_patterns', function ($patterns) {
 *       $patterns[] = 'example-analytics\.com';
 *       return $patterns;
 *   });
 */

namespace CookieConsent;

if (!defined('ABSPATH')) {
  exit;
}

require_once __DIR__ . '/class-consent.php';
require_once __DIR__ . '/class-scanner.php';

// シングルトン的に使う同意インスタンス
$GLOBALS['_cookie_consent'] = new Consent();

// 管理画面 Cookie スキャナー
add_action('after_setup_theme', function () {
  if (!is_admin()) {
    return;
  }
  (new Scanner())->boot();
});

// ─────────────────────────────────────────────────────────────
// ヘルパー関数
// ─────────────────────────────────────────────────────────────

/**
 * カテゴリ別の同意状態を返す
 *
 * @param string $category necessary | functional | analytics | marketing
 */
function has_cookie_consent(string $category): bool
{
  return $GLOBALS['_cookie_consent']->has($category);
}

/**
 * Cookie が存在するか（初回訪問かどうかの判定）
 */
function cookie_consent_exists(): bool
{
  return $GLOBALS['_cookie_consent']->exists();
}

/**
 * すべてのカテゴリの同意状態を返す
 *
 * @return array<string, bool>
 */
function get_cookie_consent_all(): array
{
  return $GLOBALS['_cookie_consent']->getAll();
}

/**
 * 表示するカテゴリ定義を返す（necessary は常に含まれる）
 *
 * フィルターで使用するカテゴリを絞り込める：
 *   add_filter('cookie_consent/categories', function ($cats) {
 *       // functional と analytics だけ使う場合
 *       return array_intersect_key($cats, array_flip(['functional', 'analytics']));
 *   });
 *
 * @return array<string, array{name: string, desc: string}>
 */
function cookie_consent_get_categories(): array
{
  $defaults = [
    'functional' => [
      'name' => '機能クッキー',
      'desc' => '機能クッキーは、ソーシャルメディアプラットフォームでのウェブサイトのコンテンツの共有、フィードバックの収集、その他のサードパーティの機能など、特定の機能の実行をサポートします。',
    ],
    'performance' => [
      'name' => '分析クッキー',
      'desc' => '分析クッキーは、訪問者がウェブサイトとどのように関わっているかを理解するために使用されます。これらのクッキーは、訪問者数、直帰率、トラフィックソースなどの指標に関する情報を提供することをサポートします。',
    ],
    'marketing' => [
      'name' => '広告クッキー',
      'desc' => '広告クッキーは、訪問者が以前に訪れたページに基づいてカスタマイズされた広告を提供し、広告キャンペーンの有効性を分析するために使用されます。',
    ],
  ];

  return (array) apply_filters('cookie_consent/categories', $defaults);
}

// ─────────────────────────────────────────────────────────────
// メインカラー上書き
//
// functions.php で指定する：
//   add_filter('cookie_consent/primary_color', fn() => '#2e7d32');
// ─────────────────────────────────────────────────────────────


// ─────────────────────────────────────────────────────────────
// アセット登録
// ─────────────────────────────────────────────────────────────

add_action('wp_enqueue_scripts', function () {
  $base = get_template_directory_uri() . '/modules/cookie-consent/assets/';
  $dir  = get_template_directory() . '/modules/cookie-consent/assets/';

  wp_enqueue_style(
    'cookie-consent',
    $base . 'cookie-consent.css',
    [],
    filemtime($dir . 'cookie-consent.css')
  );

  wp_enqueue_script(
    'cookie-consent',
    $base . 'cookie-consent.js',
    [],
    filemtime($dir . 'cookie-consent.js'),
    true  // フッターに出力
  );

  // メインカラー上書き（add_filter('cookie_consent/primary_color', fn() => '#2e7d32') で指定）
  $primary_color = apply_filters('cookie_consent/primary_color', '');
  if (!empty($primary_color)) {
    wp_add_inline_style('cookie-consent', ':root{--cc-primary:' . esc_attr($primary_color) . '}');
  }

  // PHP の同意状態を JS に渡す
  wp_localize_script('cookie-consent', 'CookieConsentConfig', [
    'exists'       => cookie_consent_exists(),
    'consent'      => get_cookie_consent_all(),
    'cookieName'   => Consent::COOKIE_NAME,
    'cookieExpire' => Consent::COOKIE_EXPIRE,
    'siteName'     => apply_filters('cookie_consent/site_name', get_bloginfo('name')),
    'privacyUrl'   => apply_filters('cookie_consent/privacy_url', '/privacy-policy/'),
    // 有効カテゴリキー一覧（JS側でトグルの有効/無効制御に使う）
    'enabledCategories' => array_keys(cookie_consent_get_categories()),
    'functionalHosts' => apply_filters('cookie_consent/functional_hosts', [
      'youtube.com',
      'youtube-nocookie.com',
      'maps.google',       // maps.google.com / maps.google.co.jp 等
      'maps.googleapis.com',
      'google.com/maps',   // www.google.com/maps/embed?pb=... 形式
    ]),
  ]);
});

// ─────────────────────────────────────────────────────────────
// Analytics タグブロック（非同意時にバッファリングで除去）
//
// GA4 等のタグは既存の出力元 PHP を触らず、wp_head 全体を
// ob_start/ob_get_clean でキャプチャしてから除去する。
// 複数タグが出力されていても preg_replace が全マッチを置換する。
// ─────────────────────────────────────────────────────────────

// バッファ開始（すべての wp_head 出力より先に）
add_action('wp_head', function () {
  ob_start();
}, 1);

// バッファ終了・ブロック処理（すべての wp_head 出力より後に）
add_action('wp_head', function () {
  $html = ob_get_clean();

  if (!has_cookie_consent('performance')) {
    // googletagmanager.com / clarity.ms を含む <script> タグを除去
    $blocked_patterns = apply_filters('cookie_consent/analytics_block_patterns', [
      'googletagmanager\.com',
      'clarity\.ms',
    ]);

    foreach ($blocked_patterns as $pattern) {
      // <script src="..."> 形式（async 等の属性付きも対応）
      $html = preg_replace(
        '/<script\b[^>]*' . $pattern . '[^>]*>.*?<\/script>/si',
        '',
        $html
      );
      // gtag() / dataLayer 等のインラインスクリプトも除去（直後に続く <script> ブロック）
      // ※ src タグの直後のインライン設定スクリプトを除去するため gtag キーワードで判定
    }

    // gtag( / dataLayer を含むインラインスクリプトを除去
    $html = preg_replace(
      '/<script\b[^>]*>(?=[^<]*(?:gtag\s*\(|dataLayer))[^<]*<\/script>/si',
      '',
      $html
    );
  }

  echo $html;
}, 99999);

// ─────────────────────────────────────────────────────────────
// ポップアップ HTML 出力
// ─────────────────────────────────────────────────────────────

add_action('wp_footer', function () {
  $site_name   = apply_filters('cookie_consent/site_name', get_bloginfo('name'));
  $privacy_url = apply_filters('cookie_consent/privacy_url', '/privacy-policy/');
?>
  <div id="cookie-consent-banner" class="cc-banner" aria-live="polite" aria-label="クッキー同意バナー" hidden>
    <div class="cc-banner__inner">
      <p class="cc-banner__text">
        <?php echo esc_html($site_name); ?>では、サービス改善・利便性向上のためにクッキーを使用しています。<br>
        <a href="<?php echo esc_url($privacy_url); ?>" class="cc-link">クッキーの詳細についてはこちら</a>
      </p>
      <div class="cc-banner__actions">
        <button type="button" class="cc-btn cc-btn--settings" id="cc-open-settings">詳細設定</button>
        <button type="button" class="cc-btn cc-btn--reject" id="cc-reject-all">すべて拒否</button>
        <button type="button" class="cc-btn cc-btn--accept" id="cc-accept-all">すべて許可</button>
      </div>
    </div>
  </div>

  <div id="cookie-consent-modal" class="cc-modal" aria-modal="true" aria-label="クッキー設定" role="dialog" hidden>
    <div class="cc-modal__overlay" id="cc-modal-overlay"></div>
    <div class="cc-modal__content">
      <h2 class="cc-modal__title">クッキーの設定</h2>
      <p class="cc-modal__desc">
        当社は、お客様を効率的にナビゲートし、特定の機能を実行できることを目的としてクッキーを使用しています。以下の各同意項目の下に、すべてのクッキーの詳細情報が記載されています。<br>
        <br>
        「必須」に分類されるクッキーは、サイトの基本的な機能を有効にするために不可欠であるため、お客様のブラウザに保存されます。<br>
        <br>
        また、当社は、お客様による本サイトの利用状況を分析し、お客様の好みを保存し、お客様に関連するコンテンツや広告を提供するために、サードパーティーのクッキーを使用しています。これらのクッキーは、お客様の事前の同意がある場合にのみ、お客様のブラウザに保存されます。<br>
        <br>
        お客様は、これらのクッキーの一部、または全部を有効または無効にすることができますが、一部のクッキーを無効にすると、お客様のブラウジング体験に影響を与える場合があります。
      </p>

      <div class="cc-categories">

        <div class="cc-category cc-category--required">
          <div class="cc-category__header">
            <div class="cc-category__info">
              <span class="cc-category__name">必須クッキー</span>
              <span class="cc-category__desc">必須クッキーとは、安全なログインの提供や同意設定の調整など、このサイトの基本機能を有効にするために必要なクッキーです。これらのクッキーは、個人を特定できるようなデータを保存することはありません。</span>
            </div>
            <span class="cc-toggle cc-toggle--always-on">常に有効</span>
          </div>
        </div>

        <?php foreach (cookie_consent_get_categories() as $key => $cat) : ?>
          <div class="cc-category">
            <div class="cc-category__header">
              <div class="cc-category__info">
                <span class="cc-category__name"><?php echo esc_html($cat['name']); ?></span>
                <span class="cc-category__desc"><?php echo esc_html($cat['desc']); ?></span>
              </div>
              <label class="cc-toggle" aria-label="<?php echo esc_attr($cat['name']); ?>を許可">
                <input type="checkbox" name="<?php echo esc_attr($key); ?>" class="cc-toggle__input" id="cc-<?php echo esc_attr($key); ?>">
                <span class="cc-toggle__slider"></span>
              </label>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <div class="cc-modal__actions">
        <button type="button" class="cc-btn cc-btn--reject" id="cc-modal-reject">すべてのクッキーを拒否する</button>
        <button type="button" class="cc-btn cc-btn--save" id="cc-modal-save">選択を保存</button>
        <button type="button" class="cc-btn cc-btn--accept" id="cc-modal-accept">すべてのクッキーを受け入れる</button>
      </div>

      <p class="cc-modal__privacy">
        <a href="<?php echo esc_url($privacy_url); ?>" class="cc-link">プライバシーポリシー</a>
      </p>
    </div>
  </div>

  <!-- 常時表示の設定ボタン（同意済みでもいつでも設定変更できる） -->
  <button type="button" class="cc-settings-trigger" id="cc-settings-trigger" aria-label="Cookie設定を変更">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="3"></circle>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
    </svg>
  </button>
<?php
}, 100);
