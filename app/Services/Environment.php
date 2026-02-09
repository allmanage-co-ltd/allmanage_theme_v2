<?php

namespace App\Services;

/**---------------------------------------------
 * 実行環境判定サービス
 * ---------------------------------------------
 * - 実行中の環境やアクセス元を判定するための共通サービス
 * - ローカル判定、モバイル判定、Bot 判定をまとめて提供する
 * - テンプレートや各所で $_SERVER を直接触らせないための窓口
 */
class Environment extends Service
{
  public function __construct()
  {
    //
  }

  /**
   * インスタンス生成
   *
   * - 明示的に new したい場合のためのファクトリ
   */
  public static function new(): self
  {
    return new Environment();
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

    $locals = [
      'localhost',
      '127.0.0.1',
      'web-checker',
      '.local',
    ];

    foreach ($locals as $local) {
      if (strpos($host, $local) !== false) {
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
    $agents = [
      'iPhone',
      'iPod',
      'Android',
      'dream',
      'CUPCAKE',
      'blackberry',
      'webOS',
      'incognito',
      'webmate',
    ];

    return self::matchAgent($agents);
  }

  /**
   * Bot判定
   */
  public static function isBot(): bool
  {
    $bots = [
      'Googlebot',
      'bingbot',
      'AhrefsBot',
      'Baiduspider',
      'YandexBot',
      'facebookexternalhit',
      'Hatena',
    ];

    return self::matchAgent($bots);
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
      if (stripos($ua, $word) !== false) {
        return true;
      }
    }

    return false;
  }
}