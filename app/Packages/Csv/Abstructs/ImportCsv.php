<?php

namespace App\Packages\Csv\Abstructs;

use App\Packages\Csv\Actions\ImportRunAction;

/**---------------------------------------------
 * CSVインポート 基底クラス
 * ---------------------------------------------
 * CSVファイルを読み込み投稿・メタ・タクソノミーを登録する共通処理を提供する。
 * カラム定義と投稿タイプはサブクラスに委ねる。
 * 実行処理は ImportRunAction に委譲する。
 *
 * ---------------------------------------------
 * ■ サブクラスで定義するメソッド
 * ---------------------------------------------
 * 必須:
 *   postType()    … 投稿タイプ（ルーティングキーにも使用: ?csv_import=<postType>）
 *   redirectUrl() … 完了後のリダイレクト先
 *   map()         … CSVカラム定義（カラム名 → action/type の設定）
 *
 * 省略可能（デフォルト値あり）:
 *   isAllowed()   … 実行権限（デフォルト: manage_options）
 */
abstract class ImportCsv
{
    /**
     * 投稿タイプ
     *
     * - ルーティングキーとしても使用される: ?csv_import=<postType>
     */
    abstract public static function postType(): string;

    /**
     * 完了後のリダイレクト先
     */
    abstract public function redirectUrl(): string;

    /**
     * CSVカラム定義
     */
    abstract protected function map(): array;

    /**
     * 実行権限
     *
     * - デフォルト: manage_options（管理者のみ）
     * - 権限を変更する場合のみオーバーライドする
     *   例: return current_user_can('edit_others_posts');
     */
    public static function isAllowed(): bool
    {
        return current_user_can('manage_options');
    }

    /**
     * 処理実行クエリパラメータ名
     */
    final public static function importParam(): string
    {
        return 'csv_import';
    }

    /**
     * dryRun パラメータ名（?dry_run=1 または POST で有効）
     */
    final public static function dryRunParam(): string
    {
        return 'dry_run';
    }

    /**
     * 成功時のクエリパラメータ名（?success=1）
     *
     * - クエリ値
     *   - ?success= 1 （成功）
     */
    final public static function successParam(): string
    {
        return 'success';
    }

    /**
     * CSVインポートを実行する
     *
     * - ImportRunAction に処理を委譲する
     */
    final public function handle(): void
    {
        (new ImportRunAction(
            postType: $this->postType(),
            map: $this->map(),
            isDryRun: isset($_REQUEST[$this->dryRunParam()]),
        ))->run();
    }
}
