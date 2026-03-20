<?php

namespace App\Errors;

use App\Interfaces\ThemeErrorHandlerInterface;

/**
 * 既定の WordPress 向け終了ハンドラ。
 */
final class WpDieErrorHandler implements ThemeErrorHandlerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function abort(string $message, array $context = []): never
    {
        if (function_exists('wp_die')) {
            wp_die($message);
        }

        exit($message);
    }
}
