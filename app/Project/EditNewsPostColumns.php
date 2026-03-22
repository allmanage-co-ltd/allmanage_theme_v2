<?php

namespace App\Project;

use App\Hooks\EditPostColumnsAbstract;

/**---------------------------------------------
 * 管理画面 News投稿一覧カラム編集
 * ---------------------------------------------
 * このクラスはNewsカスタム投稿を題材にした実装見本です。
 * 他の投稿タイプの実装はこのファイルを複製して各種適切に変更してください
 *
 * ---------------------------------------------
 * ■ 有効化方法
 * ---------------------------------------------
 * このクラスは親クラス経由で BootableWpHookInterface の契約に乗るため、
 * app/ 配下に置かれていれば HooksAutoLoader から自動 Boot されます。
 * 個別に bootstrap/app.php へ手動登録する必要はありません。
 */
final class EditNewsPostColumns extends EditPostColumnsAbstract
{
    /**
     * 対象の投稿タイプスラッグ
     *
     * - manage_{postType}_posts_columns フックに使用される
     * - カラムを編集したい投稿タイプのスラッグを返す
     */
    protected function postType(): string
    {
        return 'news';
    }

    /**
     * ACFフィールドを使用するかどうか
     *
     * - true の場合、ACF が有効でなければ処理をパスする
     */
    protected function useAcf(): bool
    {
        return true;
    }

    /**
     * 追加するカラムの定義
     *
     * - ['field_key' => '表示ラベル'] の連想配列で返す
     * - field_key は edit() の $column と対応させること
     * - 複数カラムを追加する場合は配列に追加する
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
     */
    public function edit($column, $post_id): void
    {
        if ($column === 'acf_is_public') {
            echo get_field('acf_is_public', $post_id) ? '公開' : '非公開';
        }
    }
}
