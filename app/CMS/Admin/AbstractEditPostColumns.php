<?php

namespace App\CMS\Admin;

use App\CMS\Plugins\Acf;

/**---------------------------------------------
 * 投稿一覧カラム追加 基底クラス
 * ---------------------------------------------
 * - 管理画面の投稿一覧にカスタムフィールドのカラムを追加する
 * - 投稿タイプごとにサブクラスを作成して使用する
 * - フック登録とカラム定義の追加はこのクラスに集約する
 * - カラム値の出力はサブクラスに委ねる
 */
abstract class AbstractEditPostColumns extends Admin
{
    /**
     * 対象の投稿タイプスラッグを返す
     *
     * - サブクラスで実装する
     * - 例: 'onsale', 'news'
     */
    abstract protected function postType(): string;

    /**
     * ACF フィールドを使用するかどうか
     *
     * - サブクラスで実装する
     * - true の場合、ACF が有効でなければフックを登録しない
     * - ACF 不要なカラムを追加する場合は false を返す
     */
    abstract protected function hasAcf(): bool;

    /**
     * 追加するカラムの定義を返す
     *
     * - サブクラスで実装する
     * - ['field_key' => 'ラベル'] の連想配列で返す
     * - 例: ['onsale_is_public' => '公開状況']
     */
    abstract protected function columns(): array;

    /**
     * 初期化処理
     *
     * - manage_{postType}_posts_columns: カラムのヘッダー定義を追加するフィルター
     * - manage_{postType}_posts_custom_column: カラムのセル値を出力するアクション
     */
    #[\Override]
    public function boot(): void
    {
        if ($this->hasAcf() && !Acf::isActive()) {
            return;
        }

        add_filter("manage_{$this->postType()}_posts_columns", $this->register(...));
        add_action("manage_{$this->postType()}_posts_custom_column", $this->edit(...), 10, 2);
    }

    /**
     * カラム定義の登録
     *
     * - columns() で定義したカラムを既存カラムに追加して返す
     * - $columns は既存カラムの連想配列（field_key => ラベル）
     */
    public function register($columns)
    {
        foreach ($this->columns() as $key => $label) {
            $columns[$key] = $label;
        }
        return $columns;
    }

    /**
     * カラム値の出力
     *
     * - サブクラスで実装する
     * - $column: 現在処理中のカラムのfield_key
     * - $post_id: 現在処理中の投稿ID
     * - 対象カラムの判定と値の出力をサブクラスで行う
     */
    abstract public function edit($column, $post_id): void;
}
