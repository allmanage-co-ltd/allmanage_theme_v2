<?php

namespace App\Hooks;

/**---------------------------------------------
 * Hookクラス
 * ---------------------------------------------
 *
 * ここではどこのUIに影響しないワードプレスの振る舞いや、
 * 出力されるHTMLソースにのみ影響するフックを集約します。
 *
 * ソースのエンキュー
 * テーマサポート関連のアクションフック
 * wp_headへの書き込み
 * ショートコード登録
 * etc
 *
 */
abstract class Hook
{
  public function __construct()
  {
    //
  }

  abstract public function boot(): void;
}