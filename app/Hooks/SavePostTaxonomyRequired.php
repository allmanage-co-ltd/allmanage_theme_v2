<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;

/**---------------------------------------------
 * タクソノミー必須チェッククラス
 * ---------------------------------------------
 * - 投稿保存時にタクソノミーが選択されているか検証する
 * - 未選択の場合は投稿を下書きに戻し、エラー通知を表示する
 * - 対象の投稿タイプとタクソノミーは Config で定義する
 */
class SavePostTaxonomyRequired implements BootableWpHookInterface
{
  private array $config;

  /**
   * wp_update_post による save_post の再帰呼び出しを防ぐフラグ
   */
  private bool $checking = false;

  public function __construct()
  {
    $this->config = Config::get('cms.taxonomy_required') ?? [];
  }

  public function boot(): void
  {
    add_action('save_post',     $this->checkTaxonomy(...));
    add_action('admin_notices', $this->showErrorNotice(...));
  }

  /**
   * タクソノミー必須チェック
   *
   * - 対象投稿タイプかつタクソノミーが未選択の場合にのみ処理を行う
   * - 未選択が検出されたら投稿を下書きに戻す
   * - wp_update_post が save_post を再発火するため $checking フラグで再帰を防ぐ
   * - リダイレクト先に taxonomy_error クエリパラメータを付与してエラー通知へ繋ぐ
   */
  public function checkTaxonomy(int $post_id): void
  {
    if ($this->checking) return;

    // auto-draft・リビジョン・ゴミ箱は除外
    $post = get_post($post_id);
    if (!$post) return;
    if (in_array($post->post_status, ['auto-draft', 'trash', 'inherit'], true)) return;
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

    $post_type = $post->post_type;
    if (!isset($this->config[$post_type])) return;

    foreach ($this->config[$post_type] as $taxonomy => $message) {
      if (!empty(wp_get_post_terms($post_id, $taxonomy))) continue;

      $this->checking = true;

      wp_update_post([
        'ID'          => $post_id,
        'post_status' => 'draft',
      ]);

      $this->checking = false;

      add_filter('redirect_post_location', function (string $location): string {
        return add_query_arg('taxonomy_error', 1, $location);
      });

      return;
    }
  }

  /**
   * エラー通知の表示
   *
   * - taxonomy_error クエリパラメータが付与されている場合にのみ表示する
   * - 未選択のタクソノミーに対応するエラーメッセージを出力する
   */
  public function showErrorNotice(): void
  {
    if (!isset($_GET['taxonomy_error'])) return;

    $post_type = get_current_screen()?->post_type ?? '';
    $message   = $this->errorMessage($post_type);

    if ($message === '') return;

    echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
  }

  /**
   * 表示するエラーメッセージを取得する
   *
   * - 現在の post_id に対してタクソノミーの未選択状態を再検証する
   * - 最初に未選択が見つかったタクソノミーのメッセージを返す
   * - 該当なければ空文字を返す
   */
  private function errorMessage(string $post_type): string
  {
    $post_id = (int) ($_GET['post'] ?? 0);

    foreach ($this->config[$post_type] ?? [] as $taxonomy => $message) {
      if ($post_id && empty(wp_get_post_terms($post_id, $taxonomy))) {
        return $message;
      }
    }

    return '';
  }
}
