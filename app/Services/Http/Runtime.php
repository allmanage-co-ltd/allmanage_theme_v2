<?php

namespace App\Services\Http;

use App\Services\Config;

/**---------------------------------------------
 * 実行環境判定サービス
 * ---------------------------------------------
 * - 実行中の環境やアクセス元を判定するための共通サービス
 * - ローカル判定、モバイル判定、Bot 判定をまとめて提供する
 * - テンプレートや各所で $_SERVER を直接触らせないための窓口
 */
class Runtime
{
  /**
   * リクエストID
   *
   * - 1リクエスト中は固定
   * - ログの紐付けに使用
   */
  public static function requestId(): string
  {
    static $requestId = null;

    if ($requestId !== null) {
      return $requestId;
    }

    if (!empty($_SERVER['HTTP_X_REQUEST_ID'])) {
      return $requestId = (string) $_SERVER['HTTP_X_REQUEST_ID'];
    }

    $requestId = \sprintf(
      '%s-%s',
      \date('YmdHis'),
      \bin2hex(\random_bytes(6))
    );

    return $requestId;
  }

  /**
   * エラーID
   */
  public static function errorId(): string
  {
    return \sprintf(
      '%s-%s',
      \date('YmdHis'),
      \bin2hex(\random_bytes(4))
    );
  }

  /**
   * local環境判定
   */
  public static function isLocal(): bool
  {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    if ($host === '') {
      return false;
    }

    foreach (Config::get('app.runtime.local', []) as $local) {
      if (str_contains((string) $host, (string) $local)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Xserver確認用URL環境判定
   */
  public static function isCheckUrl(): bool
  {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    if ($host === '') {
      return false;
    }

    foreach (Config::get('app.runtime.check_url', []) as $checkurl) {
      if (preg_match((string) $checkurl, (string) $host)) {
        return true;
      }
    }

    return false;
  }

  /**
   * モバイル判定
   */
  public static function isMobile(): bool
  {
    return self::matchAgent(Config::get('app.runtime.mobile', []));
  }

  /**
   * Bot判定
   */
  public static function isBot(): bool
  {
    return self::matchAgent(Config::get('app.runtime.robots', []));
  }

  /**
   * UAマッチ共通処理
   */
  private static function matchAgent(array $keywords): bool
  {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if ($ua === '') {
      return false;
    }

    foreach ($keywords as $word) {
      if (\stripos((string) $ua, (string) $word) !== false) {
        return true;
      }
    }

    return false;
  }
}
