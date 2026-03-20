<?php

namespace App\CMS\Plugins;

use App\Interfaces\BootableThemeComponentInterface;

/**
 * プラグイン連携クラスの基底。
 *
 * WordPress プラグイン有無の判定や、
 * プラグイン固有フックの登録を担当する。
 */
abstract class Plugin implements BootableThemeComponentInterface
{
    public function __construct()
    {
        // 継承先で必要なら有効判定などを行う。
    }

    /**
     * プラグイン連携用のフック登録を行う。
     */
    abstract public function boot(): void;
}
