<?php

namespace App\UseCase;

use App\CMS\Admin\AbstractEditPostColumns;

/**---------------------------------------------
 * 管理画面 投稿一覧カラム編集
 * ---------------------------------------------
 * ※ このクラスはサンプルです。他の投稿タイプで使う場合は
 *    このファイルをコピーして以下を変更してください
 *    - ファイル名・クラス名: Edit{投稿タイプ名}PostColumns.php
 *    - postType()・columns()・edit() の中身
 *
 * ---------------------------------------------
 * ■ 使い方
 * ---------------------------------------------
 * bootstrap/app.php でインスタンス化して boot() を呼ぶ:
 *
 *   (new \App\UseCase\Edit{投稿タイプ名}PostColumns())->boot();
 */
final class EditNewsPostColumns extends AbstractEditPostColumns
{
    /**
     * 対象の投稿タイプスラッグ
     *
     * - manage_{postType}_posts_columns フックに使用される
     * - 投稿タイプのスラッグを返す
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
     */
    public function edit($column, $post_id): void
    {
        if ($column === 'acf_is_public') {
            echo get_field('acf_is_public', $post_id) ? '公開' : '非公開';
        }
    }
}
