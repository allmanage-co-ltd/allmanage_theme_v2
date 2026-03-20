<?php

namespace App\CMS\Hooks;

use App\Interfaces\BootableThemeComponentInterface;

/**
 * WordPress フック登録クラスの基底。
 *
 * UI ではなく、テーマ全体の挙動や出力に関わるフック登録を担当する。
 */
abstract class Hook implements BootableThemeComponentInterface
{
    /**
     * フック登録を行う。
     */
    abstract public function boot(): void;
}
