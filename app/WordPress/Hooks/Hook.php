<?php

namespace App\WordPress\Hooks;

use App\Interfaces\BootableWpHookInterface;

/**
 * テーマ全体の WordPress フックをまとめる基底クラス。
 *
 * 画面固有ではなく、テーマの挙動そのものに関わる初期化をここに置く。
 */
abstract class Hook implements BootableWpHookInterface
{
    abstract public function boot(): void;
}
