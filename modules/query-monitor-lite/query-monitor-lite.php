<?php

/**
 * query-monitor-lite — クエリモニター（開発者向け）
 *
 * ローカル・ステージング環境限定で画面下部に固定バーを表示する。
 * クエリ数・合計時間・遅いクエリ（閾値超え）を一覧できる。
 *
 * PHP 7.4 以上 / WordPress 5.0 以上。外部依存なし。
 *
 * 詳細表示（クエリ内容・個別時間）を有効にするには wp-config.php に追記：
 *   define('SAVEQUERIES', true);
 *
 * カスタマイズ：
 *   // 遅いクエリの閾値を変更（デフォルト 0.05 秒）
 *   define('QML_SLOW_THRESHOLD', 0.1);
 *
 *   // 表示する環境を追加（デフォルトはローカル・ステージングのみ）
 *   add_filter('qml/enabled', function ($enabled) {
 *       return true; // 常に表示
 *   });
 */

namespace QueryMonitorLite;

if (!defined('ABSPATH')) {
    exit;
}

// 遅いクエリの閾値（秒）
if (!defined('QML_SLOW_THRESHOLD')) {
    define('QML_SLOW_THRESHOLD', 0.05);
}

// ローカルと判定するホスト
define('QML_LOCAL_HOSTS', [
    'localhost',
    '127.0.0.1',
    '.local',
    '.test',
]);

// ステージングと判定するホスト
define('QML_STAGING_HOSTS', [
    'web-checker',
    '.check-xserver.jp',
    'staging.',
    'stg.',
    'dev.',
    'preview.',
]);

require_once __DIR__ . '/class-monitor.php';

add_action('after_setup_theme', function () {
    // SAVEQUERIES を強制的に有効にしてクエリ計測を開始
    if (!defined('SAVEQUERIES')) {
        define('SAVEQUERIES', true);
    }
    $monitor = new Monitor();
    $monitor->boot();
});
