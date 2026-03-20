<?php

namespace App\Interfaces;

/**
 * WordPress 起動クラスの共通ルール。
 *
 * hook・admin・plugin など、boot() で初期化するクラスは
 * すべてこのインターフェースを実装する。
 */
interface BootableWpHookInterface
{
    public function boot(): void;
}
