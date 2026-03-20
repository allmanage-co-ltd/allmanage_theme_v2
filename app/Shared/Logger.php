<?php

namespace App\Support;

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
     * アプリログ（日別）
     */
    public static function app(): MonoLogger
    {
        return self::$app ??= self::createLogger('app', 'logger.use_app_log', 'logger.app_log_dir');
    }

    /**
     * アクセスログ（日別）
     */
    public static function access(): MonoLogger
    {
        return self::$access ??= self::createLogger('access', 'logger.use_access_log', 'logger.access_log_dir');
    }

    /**
     * エラーログ（日別）
     */
    // public static function error(): MonoLogger
    // {
    //     return self::$access ??= self::createLogger('error', 'logger.use_error_log', 'logger.error_log_dir');
    // }

    /**
     * Monolog インスタンスを生成する
     *
     * - configのフラグが false の場合はインスタンスを生成しない
     * - ログディレクトリが存在しない場合は作成する
     */
    private static function createLogger(string $channel, string $config_use, string $config_dir): ?MonoLogger
    {
        if (!Config::get($config_use)) {
            return null;
        }

        $dir = Path::root() . Config::get($config_dir);

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException("ログのディレクトリ作成に失敗しました: {$dir}");
        }

        $file   = $dir . '/' . date('Y-m-d') . '.log';
        $logger = new MonoLogger($channel, [], [], new \DateTimeZone('Asia/Tokyo'));
        $logger->pushHandler(new StreamHandler($file, MonoLogger::INFO));

        return $logger;
    }
}
