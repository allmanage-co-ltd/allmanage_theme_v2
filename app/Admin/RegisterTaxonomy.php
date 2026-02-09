<?php

namespace App\Admin;

use App\Services\Config;

/**---------------------------------------------
 * カスタムタクソノミー登録クラス
 * ---------------------------------------------
 * - 管理画面側で使用する Admin クラス
 * - タクソノミー定義はコードに直書きしない
 * - 設定値（Config）からまとめて登録する
 * - register_taxonomy を散らさない
 * - 投稿タイプとの紐付けを設定ファイル側に寄せる
 * - functions.php に一切書かない
 * - タクソノミー追加・削除を設定変更だけで完結させる
 */
class RegisterTaxonomy extends Admin
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
   * カスタムタクソノミー登録処理
   *
   * - Config::get('admin.taxonomies') に定義された設定を元に
   *   register_taxonomy をループで実行する
   *
   * 想定される設定例：
   * [
   *   'news_cat' => [
   *     'post_type' => ['news'],
   *     'hierarchical' => true,
   *     ...
   *   ],
   * ]
   *
   * - post_type は register_taxonomy の第2引数として使用するため
   *   $args からは除外している
   */
  public function register(): void
  {
    foreach (Config::get('admin.taxonomies') ?? [] as $name => $args) {
      $post_type = $args['post_type'];
      unset($args['post_type']);

      register_taxonomy($name, $post_type, $args);
    }
  }
}