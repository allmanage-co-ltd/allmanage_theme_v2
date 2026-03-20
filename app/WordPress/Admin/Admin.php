<?php

namespace App\WordPress\Admin;

use App\Interfaces\BootableWpHookInterface;

/**
 * 管理画面まわりの初期化をまとめる基底クラス。
 *
 * 投稿タイプ登録や管理画面 UI 調整など、admin 側だけの処理をここに寄せる。
 */
abstract class Admin implements BootableWpHookInterface
{
    abstract public function boot(): void;
}
