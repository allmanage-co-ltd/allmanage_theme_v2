<?php

/**
 * env-badge — 環境バッジ表示モジュール
 *
 * 管理バーに「ローカル」「ステージング」「本番」バッジを表示し、
 * 誤った環境での操作を防ぐ。
 *
 * PHP 7.4 以上 / WordPress 5.0 以上。外部依存なし。
 *
 * カスタマイズ：
 *   // 環境判定ルールを上書き
 *   add_filter('env_badge/detect', function ($env) {
 *       if (defined('WP_ENV')) return WP_ENV; // 'local' | 'staging' | 'production'
 *       return $env;
 *   });
 *
 *   // ステージングのホスト条件を追加
 *   add_filter('env_badge/staging_hosts', function ($hosts) {
 *       $hosts[] = 'staging.example.com';
 *       return $hosts;
 *   });
 */

namespace EnvBadge;

if (!defined('ABSPATH')) {
  exit;
}

// ローカル環境と判定するホスト部分文字列
define('ENV_BADGE_LOCAL_HOSTS', [
  'localhost',
  '127.0.0.1',
  '.local',
  '.test',
]);

// ステージング環境と判定するホスト部分文字列
define('ENV_BADGE_STAGING_HOSTS', [
  'web-checker',
  '.check-xserver.jp',
  'staging.',
  'stg.',
  'dev.',
  'preview.',
]);

require_once __DIR__ . '/class-badge.php';

add_action('after_setup_theme', function () {
  if (!is_admin() && !is_admin_bar_showing()) {
    return;
  }
  $badge = new Badge();
  $badge->boot();
});
