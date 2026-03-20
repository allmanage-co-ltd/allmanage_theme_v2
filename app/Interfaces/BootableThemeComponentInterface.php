<?php

namespace App\Interfaces;

/**
 * テーマ起動コンポーネントの共通インターフェース。
 *
 * WordPress 依存の Hook / Admin / Plugin / Package など、
 * 起動時に `boot()` でフック登録や初期化を行うクラスをこの契約で揃える。
 */
interface BootableThemeComponentInterface
{
    public function boot(): void;
}
