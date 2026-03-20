<?php

namespace App\Interfaces;

/**
 * WordPress 起動クラス用の後方互換インターフェース。
 *
 * 既存コード・設定ファイルではこちらの名前が使われているため残しつつ、
 * 実際の契約は BootableThemeComponentInterface に集約する。
 */
interface BootableWpHookInterface extends BootableThemeComponentInterface
{
}
