<?php

namespace App\Project;

use App\WordPress\Csv\Abstracts\ImportCsvAbstract;
use App\WordPress\Csv\Enums\ImportColumnActionEnum;
use App\WordPress\Csv\Enums\ImportValueTypeEnum;
use App\Shared\Config;

/**---------------------------------------------
 * News CSVインポート
 * ---------------------------------------------
 * このクラスはNewsカスタム投稿のインポート実装見本です。
 * 他の投稿タイプはこのファイルを複製して調整してください。
 *
 * ---------------------------------------------
 * ■ インポート実行方法
 * ---------------------------------------------
 *   ?csv_import=news           ← インポート実行
 *   ?csv_import=news&dry_run=1 ← 結果ログのみ（DB更新なし）
 *
 * ---------------------------------------------
 * ■ オーバーライド可能なメソッド
 * ---------------------------------------------
 * - isAllowed()  … 実行権限（デフォルト: manage_options）
 *      public static function isAllowed(): bool {}
 */
final class ImportNewsCsv extends ImportCsvAbstract
{
    /**
     * 実行を許可するユーザー権限、もしくは条件式
     * 実行前に検証し、true の場合のみ実行可能
     *
     * - デフォルト: 管理者のみ
     * - is_admin()等、権限以外でも指定可能
     */
    public static function isAllowed(): bool
    {
        return current_user_can('edit_others_posts');
    }

    /**
     * 投稿タイプのスラッグ
     *
     * - ?csv_import=news の "news" に対応する
     */
    public static function postType(): string
    {
        return 'news';
    }

    /**
     * インポート後のリダイレクト先
     */
    public function redirectUrl(): string
    {
        return admin_url('admin.php?page=' . Config::get('cms.option_pages.csv-in-expoter.slug'));
    }

    /**
     * ---------------------------------------------
     * ■ CSVマッピング定義 のカラム定義
     * ---------------------------------------------
     * - action: ImportColumnActionEnum enum で処理種別を指定する
     *      ImportColumnActionEnum::SavePost     … 投稿フィールドとして保存（post_title など）
     *      ImportColumnActionEnum::UpdateMeta   … post_meta を更新
     *      ImportColumnActionEnum::SetTerms     … タクソノミーのタームを設定
     *      ImportColumnActionEnum::SetThumbnail … アイキャッチ画像を設定
     *
     * - type: ImportValueTypeEnum enum で値の変換方法を指定する（省略可）
     *      ImportValueTypeEnum::Text            … そのまま（デフォルト）
     *      ImportValueTypeEnum::Bool            … true_values に一致すれば 1、それ以外は 0
     *      ImportValueTypeEnum::Array           … explode で配列化
     *      ImportValueTypeEnum::Gallery         … explode してURLをattachment_idに変換した配列
     */
    protected function map(): array
    {
        return [
            'post_id'        => ['action' => ImportColumnActionEnum::SavePost],
            'post_status'    => ['action' => ImportColumnActionEnum::SavePost],
            'post_title'     => ['action' => ImportColumnActionEnum::SavePost],
            'post_content'   => ['action' => ImportColumnActionEnum::SavePost],
            'post_date'      => ['action' => ImportColumnActionEnum::SavePost],

            'post_thumbnail' => [
                'action' => ImportColumnActionEnum::SetThumbnail,
            ],

            'news_cat'       => [
                'action'   => ImportColumnActionEnum::SetTerms,
                'taxonomy' => 'news_cat',
                'explode'  => ',',
            ],

            'acf_is_public'  => [
                'action'      => ImportColumnActionEnum::UpdateMeta,
                'type'        => ImportValueTypeEnum::Bool,
                'true_values' => ['公開', '1', true],
            ],
            'acf_price'      => [
                'action' => ImportColumnActionEnum::UpdateMeta,
                'type'   => ImportValueTypeEnum::Text,
            ],
            'acf_check'      => [
                'action' => ImportColumnActionEnum::UpdateMeta,
                'type'   => ImportValueTypeEnum::Array,
            ],
        ];
    }
}
