<?php

namespace App\Services;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;

/**---------------------------------------------
 * ログ出力サービス
 * ---------------------------------------------
 * - Monolog を使ったログ出力の窓口
 * - テーマ全体で共通の Logger インスタンスを使い回す
 * - ログの出力先やレベルをここで一元管理する
 */
class Logger extends Service
{
  public function __construct()
  {
    //
  }

  // Logger インスタンス保持用
  private static ?MonoLogger $logger = null;

  /**
   * Logger 取得
   */
  public static function new(): MonoLogger
  {
    if (self::$logger !== null) {
      return self::$logger;
    }

    $out = theme_dir() . '/logs/app.log';

    self::$logger = new MonoLogger('app');
    self::$logger->pushHandler(
      new StreamHandler($out, MonoLogger::INFO)
    );

    return self::$logger;
  }
}