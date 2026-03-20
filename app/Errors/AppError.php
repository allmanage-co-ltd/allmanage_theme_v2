<?php

namespace App\Errors;

use App\Interfaces\ThemeErrorHandlerInterface;

/**
 * テーマ全体で使う終了系エラーのファサード。
 *
 * WordPress 依存の `wp_die()` はここに閉じ込め、
 * 実装差し替えは setHandler() だけで完結させる。
 */
final class AppError
{
    private static ?ThemeErrorHandlerInterface $handler = null;

    public static function setHandler(ThemeErrorHandlerInterface $handler): void
    {
        self::$handler = $handler;
    }

    public static function resetHandler(): void
    {
        self::$handler = null;
    }

    public static function handler(): ThemeErrorHandlerInterface
    {
        return self::$handler ??= new WpDieErrorHandler();
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function abort(string $message, array $context = []): never
    {
        self::handler()->abort($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function fromThrowable(\Throwable $throwable, array $context = []): never
    {
        $context['exception'] = $throwable::class;

        self::abort($throwable->getMessage(), $context);
    }
}
