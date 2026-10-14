<?php

/**
 * AIを活用して試験的に作成した「便利機能集」の一括読み込み
 * 過去テーマやPHP7.4系で使いまわす想定の機能としてあえてテーマから独立させています。
 * 今後も暇なとき増やしていきます。
 */
require_once get_template_directory() . '/modules/index.php';

/**
 * Cookie 同意管理モジュール設定
 * modules/cookie-consent/cookie-consent.php のカスタマイズ
 *
 * ─ 表示カテゴリ ────────────────────────────────────────────────
 * 使用しないカテゴリはコメントアウトする。
 * functional : YouTube / Google Maps 等の iframe をブロック
 * performance  : GA4 / Clarity 等の計測タグをブロック
 * marketing  : Meta Pixel / Google Ads 等の広告タグをブロック
 */
add_filter('cookie_consent/categories', function ($cats) {
  return array_intersect_key($cats, array_flip([
    'functional', // 機能性クッキーON/OFF表示
    'performance', // 分析用クッキーON/OFF表示
    // 'marketing', // 広告用クッキーON/OFF表示
  ]));
});

/** バナーに表示するサイト名 */
add_filter('cookie_consent/site_name', fn() => 'ALLMANAGE');

/** プライバシーポリシーページの URL */
add_filter('cookie_consent/privacy_url', fn() => '/privacypolicy/');

/** サイトのメインカラー（許可ボタン・トグル ON・リンク色に適用） */
add_filter('cookie_consent/primary_color', fn() => '#FC7231');

/** クッキーを許可しない直タグを削除するフィルター */
add_filter('cookie_consent/analytics_block_patterns', function ($patterns) {
  $patterns[] = 'connect\.facebook\.net';  // Meta Pixel 直タグ
  $patterns[] = 'googleadservices\.com';    // Google Ads 直タグ
  $patterns[] = 'satori-cloud\.com';        // SATORI
  return $patterns;
});
