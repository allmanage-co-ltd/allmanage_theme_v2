<?php

namespace App\Services\Csv;

use App\Error\AppError;
use App\Services\Csv\Actions\ImportRunAction;

/**---------------------------------------------
 * CSVインポート 基底クラス
 * ---------------------------------------------
 * CSVファイルを読み込み投稿・メタ・タクソノミーを登録する共通処理を提供する。
 * カラム定義と投稿タイプはサブクラスに委ねる。
 * 実行処理は ImportRunAction に委譲する。
 *
 * 実装クラスはこの Abstract を継承する。
 * ImportCsvHook からは ImportCsvAbstract のサブクラスとして扱われる。
 */
abstract class ImportCsvAbstract
{
  /**
   * 投稿タイプ
   *
   * - ルーティングキーとしても使用される（POSTパラメータ: csv_import=<postType>）
   */
  abstract public static function postType(): string;

  /**
   * 完了後のリダイレクト先
   */
  abstract public static function redirectUrl(): string;

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
    try {
      (new ImportRunAction(
        reader: new CsvReader(),
        postType: static::postType(),
        map: $this->map(),
        isDryRun: isset($_REQUEST[static::dryRunParam()]),
      ))->run();
    } catch (\Throwable $throwable) {
      AppError::abort($throwable);
    }
  }
}
