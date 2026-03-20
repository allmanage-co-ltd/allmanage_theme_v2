<?php

namespace App\WordPress\Features\Csv;

use App\Interfaces\CsvImporterInterface;
use App\Shared\CsvReader;
use App\WordPress\Features\Csv\Actions\ImportRunAction;

/**---------------------------------------------
 * CSVインポート 基底クラス
 * ---------------------------------------------
 * CSVファイルを読み込み投稿・メタ・タクソノミーを登録する共通処理を提供する。
 * カラム定義と投稿タイプはサブクラスに委ねる。
 * 実行処理は ImportRunAction に委譲する。
 *
 * Hook からは CsvImporterInterface で扱い、
 * 実装側だけがこの Abstract を継承する。
 */
abstract class ImportCsvAbstract implements CsvImporterInterface
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

    public static function isAllowed(): bool
    {
        return true;
    }

    final public static function importParam(): string
    {
        return 'csv_import';
    }

    final public static function dryRunParam(): string
    {
        return 'dry_run';
    }

    final public static function successParam(): string
    {
        return 'success';
    }

    final public function handle(): void
    {
        (new ImportRunAction(
            reader: new CsvReader(),
            postType: static::postType(),
            map: $this->map(),
            isDryRun: isset($_REQUEST[static::dryRunParam()]),
        ))->run();
    }
}
