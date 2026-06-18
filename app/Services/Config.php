<?php

namespace App\Services;

use App\Helpers\Arr;
use App\Helpers\Path;

/**---------------------------------------------
 * 設定取得サービス
 * ---------------------------------------------
 * - テーマ用の設定ファイル（/config/*.php）を読み込む
 * - ドット記法でネストした設定値を取得できる
 * - 設定ファイルは一度だけ読み込み、以降はキャッシュを使う
 */
class Config
{
  /**
   * 設定値取得
   *
   * - 第1階層はファイル名（config/{file}.php）
   * - 第2階層以降は配列キーをドット区切りで指定
   * - 存在しない場合は $default を返す
   */
  public static function get(string $key, mixed $default = null): mixed
  {
    static $configs = [];

    $parts = \explode('.', $key, 2);
    $file  = $parts[0] ?? null;
    $path  = $parts[1] ?? null;

    if (!$file) {
      return $default;
    }

    if (!\array_key_exists($file, $configs)) {
      $configPath = Path::config("{$file}.php");

      if (!\file_exists($configPath)) {
        return $default;
      }

      $loaded = require $configPath;

      if (!\is_array($loaded)) {
        return $default;
      }

      $configs[$file] = $loaded;
    }

    return Arr::get($configs[$file], $path, $default);
  }
}
