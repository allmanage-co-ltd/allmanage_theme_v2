<?php

namespace App\Services;

/**---------------------------------------------
 * 設定取得サービスクラス
 * ---------------------------------------------
 * - テーマ用の設定ファイル（/config/*.php）を読み込む
 * - ドット記法でネストした設定値を取得できる
 * - 設定ファイルは一度だけ読み込み、以降はキャッシュを使う
 *
 * 例：
 * - Config::get('assets.version')
 * - Config::get('admin.post_types')
 * - Config::get('csv.show_admin_menu', false)
 */
class Config extends Service
{
  public function __construct()
  {
    //
  }

  /**
   * 設定値取得
   *
   * - 第1階層はファイル名（config/{file}.php）
   * - 第2階層以降は配列キーをドット区切りで指定
   * - 存在しない場合は $default を返す
   */
  public static function get(string $key, $default = null): mixed
  {
    static $configs = [];

    $parts = explode('.', $key, 2);
    $file  = $parts[0] ?? null;
    $path  = $parts[1] ?? null;

    if (!$file) {
      return $default;
    }

    if (!array_key_exists($file, $configs)) {
      $config_path = theme_dir() . "/config/{$file}.php";

      if (!file_exists($config_path)) {
        return $default;
      }

      $loaded = require $config_path;

      if (!is_array($loaded)) {
        return $default;
      }

      $configs[$file] = $loaded;
    }

    if ($path === null) {
      return $configs[$file];
    }

    return self::array_get($configs[$file], $path, $default);
  }

  /**
   * 配列のドット記法アクセス
   *
   * - 多次元配列から安全に値を取得する
   * - 途中でキーが存在しない場合は $default を返す
   */
  private static function array_get(array $array, string $key, $default = null): mixed
  {
    if ($key === '') {
      return $array;
    }

    foreach (explode('.', $key) as $segment) {
      if (!is_array($array) || !array_key_exists($segment, $array)) {
        return $default;
      }
      $array = $array[$segment];
    }

    return $array;
  }
}