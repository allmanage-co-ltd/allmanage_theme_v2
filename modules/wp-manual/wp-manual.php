<?php

/**
 * WP管理画面マニュアル 自動生成モジュール エントリファイル（PHP 7.4 対応）
 *
 * functions.php に以下を追記するだけで有効化される。
 *   require_once get_template_directory() . '/inc/wp-manual/wp-manual.php';
 *
 * - サイトの構成（投稿タイプ / タクソノミー / ACF / オプションページ / 主要プラグイン）を
 *   実行時に解析し、管理画面「マニュアル」メニューに操作手順を描画する
 * - フロントには一切出力しない（管理画面内で完結）
 * - 編集者以上：各セクションの「補足メモ」と「困ったときは（FAQ）」を編集できる
 * - 管理者：セクションごとに表示 / 非表示、閲覧ログの確認
 * - 管理バーの「マニュアル」は今いる画面に対応するタブへ直接飛ぶ
 * - 投稿編集画面に「この画面の使い方」メタボックスを表示
 */

namespace WpManual;

if (!defined('ABSPATH')) {
    exit;
}

define('WP_MANUAL_DIR', __DIR__);
// 配置場所・子テーマに依存しないように、テーマディレクトリからの相対パスでURLを組む
define('WP_MANUAL_URL', (function () {
    $theme_dir = wp_normalize_path(get_stylesheet_directory());
    $this_dir  = wp_normalize_path(__DIR__);
    if (strpos($this_dir, $theme_dir) === 0) {
        return get_stylesheet_directory_uri() . substr($this_dir, strlen($theme_dir));
    }
    $theme_dir = wp_normalize_path(get_template_directory());
    if (strpos($this_dir, $theme_dir) === 0) {
        return get_template_directory_uri() . substr($this_dir, strlen($theme_dir));
    }
    return get_template_directory_uri() . '/inc/wp-manual';
})());
define('WP_MANUAL_MENU_SLUG', 'wp-manual');
define('WP_MANUAL_VIEW_CAP', 'edit_posts');       // 閲覧・補足メモ編集・FAQ編集ができる権限（編集者以上）
define('WP_MANUAL_ADMIN_CAP', 'manage_options');  // 項目の表示/非表示を切り替えられる権限（管理者）
define('WP_MANUAL_OPT_NOTES', 'wp_manual_notes');
define('WP_MANUAL_OPT_VISIBILITY', 'wp_manual_visibility'); // 旧 wp_manual_hidden は初回に自動移行
define('WP_MANUAL_OPT_FAQ', 'wp_manual_faq');

require_once WP_MANUAL_DIR . '/class-inspector.php';
require_once WP_MANUAL_DIR . '/class-renderer.php';
require_once WP_MANUAL_DIR . '/class-log.php';
require_once WP_MANUAL_DIR . '/class-admin.php';

// テーマから読み込まれるため plugins_loaded は使えない（既に発火済み）
add_action('after_setup_theme', function () {
    if (is_admin()) {
        Admin::instance();
    }
});
