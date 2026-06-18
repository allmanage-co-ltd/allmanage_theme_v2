<?php

namespace App\Helpers;

/**---------------------------------------------
 * 配列・区切り文字ヘルパー
 * ---------------------------------------------
 * - ドット記法アクセス
 * - 区切り文字の分割 + trim + 空要素除去
 * - 先頭 / 末尾要素の安全取得
 * をまとめる。
 */
class Arr
{
  /**
   * 配列をドット記法で取得する
   */
  public static function get(array $array, ?string $key = null, mixed $default = null): mixed
  {
    if ($key === null || $key === '') {
      return $array;
    }

    foreach (\explode('.', $key) as $segment) {
      if (!\is_array($array) || !\array_key_exists($segment, $array)) {
        return $default;
      }

      $array = $array[$segment];
    }

    return $array;
  }

  /**
   * 文字列を区切り文字で分割して整形する
   *
   * - trim / urldecode を共通化する
   * - 空文字は配列から除外する
   */
  public static function split(string $value, string $delimiter = ','): array
  {
    if ($value === '') {
      return [];
    }

    return \array_values(\array_filter(
      \array_map(
        static fn(string $item): string => \trim(\urldecode($item)),
        \explode($delimiter, $value)
      ),
      static fn(string $item): bool => $item !== ''
    ));
  }

  /**
   * 先頭要素を返す
   */
  public static function first(array $values, mixed $default = null): mixed
  {
    return $values[0] ?? $default;
  }

  /**
   * 末尾要素を返す
   */
  public static function last(array $values, mixed $default = null): mixed
  {
    if ($values === []) {
      return $default;
    }

    return $values[\array_key_last($values)] ?? $default;
  }
}
