<?php

namespace App\Helpers;

/**---------------------------------------------
 * パス取得ヘルパー
 * ---------------------------------------------
 */
class Path
{
  /**
   * ルートパス
   */
  public static function root(): string
  {
    return \realpath(\dirname(__DIR__, 2));
  }

  /**
   * appパス
   */
  public static function app(string $path = ''): string
  {
    return self::join(self::root(), 'app', $path);
  }

  /**
   * configパス
   */
  public static function config(string $path = ''): string
  {
    return self::join(self::root(), 'config', $path);
  }

  /**
   * viewsパス
   */
  public static function views(string $path = ''): string
  {
    return self::join(self::root(), 'views', $path);
  }

  /**
   * storageパス
   */
  public static function storage(string $path = ''): string
  {
    return self::join(self::root(), 'storage', $path);
  }

  /**
   * パス結合
   *
   * - 先頭 / 末尾のスラッシュ揺れを吸収する
   * - 空文字は自然に無視される
   */
  public static function join(string ...$parts): string
  {
    $first = (string) \array_shift($parts);

    return \rtrim($first, '/')
      . '/'
      . \implode('/', \array_filter(\array_map(
        static fn(string $part): string => \trim($part, '/'),
        $parts
      ), static fn(string $part): bool => $part !== ''));
  }
}
