<?php

namespace App\WordPress\Features\Csv\Actions;

use App\Enums\CsvColumnActionEnum;

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
     *
     * - isDryRun が true の場合は何もしない
     * - action が CsvColumnActionEnum でない場合は何もしない
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
            default                              => null,
        };
    }

    /**
     * タクソノミーのタームを設定する
     *
     * - taxonomy の指定がない場合は何もしない
     * - config['explode'] が指定されている場合はその区切り文字で分割する
     *   例: "お知らせ,スポーツ" → ['お知らせ', 'スポーツ']
     * - 数字文字列（"5" など）は int にキャストしてIDとして扱う。
     *   wp_set_object_terms() に文字列を渡すとスラッグとして解釈されるため、
     *   CSVにterm_idが入っている場合に意図通りに動かない。
     */
    private function setTerms(int $post_id, mixed $value, array $config): void
    {
        $taxonomy = $config['taxonomy'] ?? null;

        if (!$taxonomy) {
            return;
        }

        if (is_string($value) && isset($config['explode'])) {
            $terms = array_values(array_filter(
                array_map('trim', explode($config['explode'], $value))
            ));
        } else {
            $terms = is_array($value) ? $value : [$value];
        }

        $terms = array_map(
            fn($t) => is_numeric($t) ? (int) $t : $t,
            $terms
        );

        wp_set_object_terms($post_id, $terms, $taxonomy);
    }

    /**
     * アイキャッチ画像を設定する
     *
     * - value が空の場合は何もしない
     * - ImportAttachmentResolveAction でURLから attachment_id を解決する
     * - attachment_id が取得できない場合は何もしない
     */
    private function setThumbnail(int $post_id, mixed $value): void
    {
        if ($value === '') {
            return;
        }

        $id = (new ImportAttachmentResolveAction())($value);

        if ($id) {
            set_post_thumbnail($post_id, $id);
        }
    }
}
