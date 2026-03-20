<?php

namespace App\Errors;

use App\Interfaces\AbortAppErrorInterface;

/**
 * テーマ内で処理を止めたい時の共通窓口。
 *
 * `wp_die()` を直接散らさず、このクラスだけを呼ぶようにしておくと
 * 後で終了方法を変えたい時も修正箇所をここに寄せられる。
 */
final class AppError implements AbortAppErrorInterface
{
    public static function abort(string $message): never
    {
        if (function_exists(__NAMESPACE__ . '\\wp_die')) {
            wp_die($message);
        }

        if (function_exists('wp_die')) {
            wp_die($message);
        }

        exit($message);
    }

    public static function fromThrowable(\Throwable $throwable): never
    {
        self::abort($throwable->getMessage());
    }
}
