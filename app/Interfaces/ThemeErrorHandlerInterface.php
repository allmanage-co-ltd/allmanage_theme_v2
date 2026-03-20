<?php

namespace App\Interfaces;

/**
 * テーマ全体の終了系エラーを扱うインターフェース。
 *
 * `wp_die()` 依存を直接散らさず、
 * WordPress 実行環境・CLI・テストで差し替えやすくするための窓口。
 */
interface ThemeErrorHandlerInterface
{
    /**
     * メッセージを表示して処理を終了する。
     *
     * @param array<string, mixed> $context
     */
    public function abort(string $message, array $context = []): never;
}
