<?php

namespace App\UseCase\Csv\Import;

use App\Enums\Csv\CsvImportAction;
use App\Enums\Csv\CsvImportValueType;

/**---------------------------------------------
 * News CSVインポート
 * ---------------------------------------------
 * このクラスはNewsカスタム投稿のインポート実装見本です。
 * 他の投稿タイプはこのファイルを複製して調整してください。
 *
 * ---------------------------------------------
 * ■ インポート実行方法
 * ---------------------------------------------
 * フォームアクション経由のURLアクセス: config/csv.phpへのクラス登録必須
 *
 *   <form method="post" enctype="multipart/form-data" action="?csv_import=news&dry_run=1">
 *       <input type="file" name="csv">
 *       <button type="submit">CSVインポート</button>
 *   </form>
 *
 *   ?csv_import=news         ← インポート実装
 *   ?csv_import=news?dry_run ← 結果ログのみデバッグ
 *
 * ※ config/csv.php の 'importer' に必ずクラス文字列を登録してください。
 *
 *   'importer' => [
 *       \App\UseCase\ImportNewsCsv::class,
 *   ]
 *
 * → importerに登録されたクラスの中から
 *   key() と一致するクラスの handle() が実行される
 *
 * ---------------------------------------------
 * ■ dryRun（検証モード）
 * ---------------------------------------------
 * URLにクエリを付与することでDB更新せず検証可能
 *
 *   ?csv_import=news&dry_run=1
 *
 * - 投稿は保存されない
 * - サムネ・タクソノミー・メタの変換結果を確認できる
 *
 * ---------------------------------------------
 * ■ 想定CSVフォーマット（自由に変更してください）
 * ---------------------------------------------
 * post_id,post_status,post_title,post_content,post_date,post_thumbnail,news_cat,acf_is_public,acf_price
 *
 * ---------------------------------------------
 * ■ 仕様
 * ---------------------------------------------
 * - post_id があれば更新、なければ新規作成
 * - post_thumbnail はURLからattachment_idを解決して設定
 * - news_cat はカンマ区切りで複数指定可能
 * - acf_is_public はBOOL変換
 */
final class ImportNewsCsv extends AbstractImportCsv
{
    /**
     * 実行権限
     *
     * - 他ユーザー投稿を編集できるユーザーのみ許可
     */
    protected function auth(): bool
    {
        return current_user_can('edit_others_posts');
    }

    /**
     * dryRunモード
     *
     * - ?dry_run=1 が付与されている場合は実行せずログ出力のみ
     */
    protected function dryRun(): bool
    {
        return isset($_REQUEST['dry_run']);
    }

    /**
     * 投稿タイプ
     *
     * - ?csv_import=news の "news" に対応
     */
    protected function postType(): string
    {
        return 'news';
    }

    /**
     * CSVマッピング定義
     */
    protected function map(): array
    {
        return [
            'post_id' => [
                'action' => CsvImportAction::SAVE_POST,
            ],
            'post_status' => [
                'action' => CsvImportAction::SAVE_POST,
            ],
            'post_title' => [
                'action' => CsvImportAction::SAVE_POST,
            ],
            'post_content' => [
                'action' => CsvImportAction::SAVE_POST,
            ],
            'post_date' => [
                'action' => CsvImportAction::SAVE_POST,
            ],
            'post_thumbnail' => [
                'action' => CsvImportAction::SET_THUMBNAIL,
            ],
            'news_cat' => [
                'action'   => CsvImportAction::SET_TERMS,
                'taxonomy' => 'news_cat',
                'explode'  => ',',
            ],
            'acf_is_public' => [
                'action'      => CsvImportAction::UPDATE_META,
                'type'        => CsvImportValueType::BOOL,
                'true_values' => ['公開', '1', true],
            ],
            'acf_price' => [
                'action' => CsvImportAction::UPDATE_META,
                'type'   => CsvImportValueType::TEXT,
            ],
            // 'acf_check' => [
            //     'action'  => CsvImportAction::UPDATE_META,
            //     'type'    => CsvImportValueType::ARRAY,
            //     'explode' => ',',
            // ],
            // 'acf_gallery' => [
            //     'action'  => CsvImportAction::UPDATE_META,
            //     'type'    => CsvImportValueType::GALLERY,
            //     'explode' => ',',
            // ],
        ];
    }
}
