<?php

namespace App\UseCase;

use App\CMS\Admin\EditPostColumnsAbstract;

/**---------------------------------------------
 * 管理画面カスタム投稿タイプのカラムを編集
 * ---------------------------------------------
 * - 管理画面の投稿一覧に任意のカラムを追加する
 * - この EditNewsPostColumns は実装サンプルです。
 *   他投稿タイプは本ファイルを参考に別ファイルを作成してください。
 *   - app/UseCase/Edit{投稿タイプ名}PostColumns.php
 *
 * ※ bootstrap/app.php でインスタンス初期化必須
 *
 *   (new Edit{投稿タイプ名}PostColumns())->boot();
 */
final class EditNewsPostColumns extends EditPostColumnsAbstract
{
    /**
     * 対象の投稿タイプスラッグ
     *
     * - manage_{postType}_posts_columns フックに使用される
     * - 投稿タイプのスラッグを返す（register_post_type で登録したもの）
     */
    protected function postType(): string
    {
        return 'news';
    }

    /**
     * ACFフィールドを使用するかどうか
     *
     * - true の場合、ACF が有効でなければフックを登録しない
     * - カスタムフィールドを使わないカラムを追加する場合は false を返す
     */
    protected function hasAcf(): bool
    {
        return true;
    }

    /**
     * 追加するカラムの定義
     *
     * - ['field_key' => '表示ラベル'] の連想配列で返す
     * - field_key は edit() の $column と対応させること
     * - 複数カラムを追加する場合は配列に追加する
     *
     * 例:
     *   return [
     *       'acf_is_public' => '公開状況',
     *       'acf_priority'  => '優先度',
     *   ];
     */
    protected function columns(): array
    {
        return [
            'acf_is_public' => '公開状況',
        ];
    }

    /**
     * カラム値の出力
     *
     * - $column が対象の field_key と一致する場合に値を出力する
     * - columns() で定義した field_key の数だけ if/match で処理を追加する
     * - ACFフィールドは get_field(field_key, post_id) で取得する
     *
     * 例（複数カラムの場合）:
     *   match ($column) {
     *       'acf_is_public' => print(get_field('acf_is_public', $post_id) ? '公開' : '非公開'),
     *       'acf_priority'  => print(get_field('acf_priority', $post_id) ?: '−'),
     *       default         => null,
     *   };
     */
    public function edit($column, $post_id): void
    {
        if ($column === 'acf_is_public') {
            echo get_field('acf_is_public', $post_id) ? '公開' : '非公開';
        }
    }
}
