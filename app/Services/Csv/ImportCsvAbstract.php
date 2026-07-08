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
    $isDryRun = isset($_REQUEST[static::dryRunParam()]);

    // Ajax リクエスト時はストリーミングで進捗を返す
    if ($this->isAjax()) {
      $this->handleStreaming($isDryRun);
      return;
    }

    try {
      (new ImportRunAction(
        reader: new CsvReader(),
        postType: static::postType(),
        map: $this->map(),
        isDryRun: $isDryRun,
      ))->run();
    } catch (\Throwable $throwable) {
      AppError::abort($throwable);
    }
  }

  /**
   * Ajax インポート：ndjson ストリームで進捗を逐次出力する
   *
   * - 1行処理するたびに {"processed":N,"total":M,"row":{...}} を出力する
   * - 完了時は {"done":true,"redirectUrl":"..."} を出力する
   * - dryRun 時は {"done":true,"dryRun":true,"log":[...]} を出力する
   */
  private function handleStreaming(bool $isDryRun): void
  {
    \ob_implicit_flush(true);

    // 出力バッファをすべてフラッシュしてストリーミングを開始する
    while (\ob_get_level() > 0) {
      \ob_end_flush();
    }

    \header('Content-Type: application/x-ndjson; charset=utf-8');
    \header('X-Accel-Buffering: no');
    \header('Cache-Control: no-cache');

    $log = [];

    $onProgress = function (array $progress) use ($isDryRun, &$log): void {
      if ($isDryRun) {
        $log[] = $progress['row'];
        return;
      }

      echo \json_encode([
        'processed' => $progress['processed'],
        'total'     => $progress['total'],
        'title'     => $progress['row']['title'] ?? '',
        'post_id'   => $progress['row']['post_id'] ?? 0,
      ]) . "\n";

      \flush();
    };

    try {
      (new ImportRunAction(
        reader: new CsvReader(),
        postType: static::postType(),
        map: $this->map(),
        isDryRun: $isDryRun,
        onProgress: $onProgress,
      ))->run();
    } catch (\Throwable $throwable) {
      echo \json_encode(['error' => $throwable->getMessage()]) . "\n";
      \flush();
      exit;
    }

    if ($isDryRun) {
      echo \json_encode(['done' => true, 'dryRun' => true, 'log' => $log]) . "\n";
    } else {
      echo \json_encode(['done' => true, 'redirectUrl' => static::redirectUrl()]) . "\n";
    }

    \flush();
    exit;
  }

  /**
   * Ajax リクエスト判定（X-Requested-With ヘッダーで判断）
   */
  private function isAjax(): bool
  {
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
  }
}
