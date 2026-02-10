<?php

namespace App\Helpers;

/**---------------------------------------------
 *
 * ---------------------------------------------
 */
class Fmt
{
    // HTMLエスケープ
    public static function h(string $s, $enc = 'UTF-8'): string
    {
        return htmlspecialchars($s, ENT_QUOTES, $enc, false);
    }

    // 改行
    public static function nl2br(string $s): string
    {
        return nl2br(self::h($s));
    }

    // 数値
    public static function number(int|float $n): string
    {
        return number_format($n);
    }

    // 日付表示
    public static function date(string $date): string
    {
        return date('Y年n月j日', strtotime($date));
    }

    // 配列 → 表示文字列
    public static function joinstr(array $values, string $sep = '、'): string
    {
        return implode($sep, $values);
    }

    // 文字数制限
    public static function truncate(string $s, int $len): string
    {
        return mb_strimwidth($s, 0, $len, '…', 'UTF-8');
    }

}
