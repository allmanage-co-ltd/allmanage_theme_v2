<?php

namespace App\Services\Csv\Actions;

use App\Enums\CsvColumnActionEnum;
use App\Helpers\Arr;

/**---------------------------------------------
 * カラム単位のアクション実行
 * ---------------------------------------------
 * map() で指定された action に応じた WordPress 処理を実行する invokable クラス。
 *
 * 対象アクション:
 *   updateMeta   … update_post_meta でカスタムフィールドを更新する
 *   setTerms     … wp_set_object_terms でタクソノミーのタームを設定する
 *   setThumbnail … set_post_thumbnail でアイキャッチ画像を設定する
 *
 * savePost はこのクラスの対象外（ImportPostSaveAction が担う）。
 * dryRun 時は何も実行しない。
 */
class ImportColumnAction
{
  public function __construct(
    private readonly bool $isDryRun,
  ) {
    //
  }

  /**
   * アクションを実行する
   */
  public function __invoke(int $post_id, string $key, mixed $value, array $config): void
  {
    if ($this->isDryRun) {
      return;
    }

    $action = $config['action'] ?? null;

    if (!$action instanceof CsvColumnActionEnum) {
      return;
    }

    match ($action) {
      CsvColumnActionEnum::UpdateMeta   => update_post_meta($post_id, $key, $value),
      CsvColumnActionEnum::SetTerms     => $this->setTerms($post_id, $value, $config),
      CsvColumnActionEnum::SetThumbnail => $this->setThumbnail($post_id, $value),
      default                           => null,
    };
  }

  /**
   * タクソノミーのタームを設定する
   */
  private function setTerms(int $post_id, mixed $value, array $config): void
  {
    $taxonomy = $config['taxonomy'] ?? null;

    if (!$taxonomy) {
      return;
    }

    if (\is_string($value) && isset($config['explode'])) {
      $terms = Arr::split($value, $config['explode']);
    } else {
      $terms = \is_array($value) ? $value : [$value];
    }

    $terms = \array_map(
      static fn(mixed $term): mixed => \is_numeric($term) ? (int) $term : $term,
      $terms
    );

    wp_set_object_terms($post_id, $terms, $taxonomy);
  }

  /**
   * アイキャッチ画像を設定する
   */
  private function setThumbnail(int $post_id, mixed $value): void
  {
    if ($value === '') {
      return;
    }

    $id = (new ImportAttachmentResolveAction($this->isDryRun))($value);

    if ($id) {
      set_post_thumbnail($post_id, $id);
    }
  }
}
