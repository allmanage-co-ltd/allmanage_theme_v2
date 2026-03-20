<?php

namespace App\CMS\Admin;

use App\Interfaces\BootableThemeComponentInterface;

/**
 * WordPress 管理画面向けクラスの基底。
 *
 * 管理画面の構成・UI・投稿タイプ登録など、
 * admin 側に閉じた起動処理をここに集約する。
 */
abstract class Admin implements BootableThemeComponentInterface
{
    /**
     * 管理画面向けのフック登録を行う。
     */
    abstract public function boot(): void;
}
