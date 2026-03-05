<?php

namespace App\Support;

use App\Support\Path;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonoLogger;

/**---------------------------------------------
 * ログ出力サービス
 * ---------------------------------------------
 * - Monolog を使ったログ出力の窓口
 * - テーマ全体で共通の Logger インスタンスを使い回す
 * - ログの出力先やレベルをここで一元管理する
 */
class Logger
{
    private static ?MonoLogger $app = null;
    private static ?MonoLogger $access = null;

    /**
     * アプリログ
     */
    public static function app(): MonoLogger
    {
        if (self::$app !== null) {
            return self::$app;
        }

        $file = Path::root() . Config::get('app.log');

        self::$app = new MonoLogger(
            'app',
            [],
            [],
            new \DateTimeZone('Asia/Tokyo')
        );
        self::$app->pushHandler(
            new StreamHandler($file, MonoLogger::INFO)
        );

        return self::$app;
    }

    /**
     * アクセスログ（日別）
     */
    public static function access(): MonoLogger
    {
        if (self::$access !== null) {
            return self::$access;
        }

        $dir = Path::root() . Config::get('app.access_log_dir');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir . '/' . date('Y-m-d') . '.log';

        self::$access = new MonoLogger(
            'access',
            [],
            [],
            new \DateTimeZone('Asia/Tokyo')
        );
        self::$access->pushHandler(
            new StreamHandler($file, MonoLogger::INFO)
        );

        return self::$access;
    }
}
