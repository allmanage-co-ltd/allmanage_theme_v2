<?php

namespace App\WordPress\Plugins;

use App\Interfaces\BootableWpHookInterface;

/**
 * プラグイン連携用の基底クラス。
 *
 * プラグイン有効判定や、そのプラグイン専用のフック登録をここにまとめる。
 */
abstract class Plugin implements BootableWpHookInterface
{
    public function __construct()
    {
        // 継承先で必要なら初期判定を行う。
    }

    abstract public function boot(): void;
}
