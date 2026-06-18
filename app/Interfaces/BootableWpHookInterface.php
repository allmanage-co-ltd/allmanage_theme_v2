<?php

namespace App\Interfaces;

/**
 * WordPress 起動クラスの共通ルール。
 *
 * hook・admin・plugin など、WordPress の起動処理に関するクラスは
 * このインターフェースを必ず実装すること
 *
 * HooksAutoLoader により app 配下のBootableWpHookInterfaceを
 * 実装したフッククラスがスキャンされ 自動 boot() される想定。
 */
interface BootableWpHookInterface
{
  public function boot(): void;
}
