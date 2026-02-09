<?php

namespace App\Admin;

use App\Services\Config;

/**---------------------------------------------
 * カスタム投稿タイプ登録クラス
 * ---------------------------------------------
 * - 管理画面側で使用する Admin クラス
 * - カスタム投稿タイプの定義はコードに直書きしない
 * - 設定値（Config）からまとめて登録する
 * - register_post_type を散らさない
 * - functions.php に書かない
 * - 投稿タイプ追加・削除は設定変更のみで完結させる
 */
class RegisterPostType extends Admin
{
  public function __construct()
  {
    //
  }

  /**
   * 初期化処理
   */
  public function boot(): void
  {
    add_action('init', [$this, 'register']);
  }

  /**
   * カスタム投稿タイプ登録処理
   *
   * - Config::get('admin.post_types') に定義された内容を元に
   *   register_post_type をループで実行する
   *
   * 想定される設定例：
   * [
   *   'news' => [...],
   *   'case' => [...],
   * ]
   *
   * - 管理画面構成を設定ファイル主導で管理するための仕組み
   */
  public function register(): void
  {
    foreach (Config::get('admin.post_types') ?? [] as $name => $args) {
      register_post_type($name, $args);
    }
  }
}