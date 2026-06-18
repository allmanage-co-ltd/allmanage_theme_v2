<?php

namespace App\Services\Logger;

use App\Helpers\Path;
use App\Services\Config;
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
  private static ?MonoLogger $error = null;

  /**
   * アプリログ（日別）
   */
  public static function app(): ?MonoLogger
  {
    return self::$app ??= self::createLogger('app', 'logger.app.use', 'logger.app.dir');
  }

  /**
   * アクセスログ（日別）
   */
  public static function access(): ?MonoLogger
  {
    return self::$access ??= self::createLogger('access', 'logger.access.use', 'logger.access.dir');
  }

  /**
   * エラーログ（日別）
   */
  public static function error(): ?MonoLogger
  {
    return self::$error ??= self::createLogger('error', 'logger.error.use', 'logger.error.dir');
  }

  /**
   * Monolog インスタンスを生成する
   */
  private static function createLogger(string $channel, string $configUse, string $configDir): ?MonoLogger
  {
    if (!Config::get($configUse)) {
      return null;
    }

    $dir = Path::root() . Config::get($configDir);

    if (!\is_dir($dir) && !\mkdir($dir, 0755, true) && !\is_dir($dir)) {
      throw new \RuntimeException("ログのディレクトリ作成に失敗しました: {$dir}");
    }

    $file   = $dir . '/' . \date('Y-m-d') . '.log';
    $logger = new MonoLogger($channel, [], [], new \DateTimeZone('Asia/Tokyo'));
    $logger->pushHandler(new StreamHandler($file, MonoLogger::INFO));

    return $logger;
  }
}
