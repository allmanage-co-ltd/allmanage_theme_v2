<?php

namespace App\Services\Csv;

use App\Error\AppError;
use App\Helpers\Html;
use App\Services\Csv\Actions\ExportGetTermSlugsAction;

/**
 * CSV エクスポートの共通処理。
 *
 * 実装クラスはこの Abstract を継承する。
 * ExportCsvHook からは ExportCsvAbstract のサブクラスとして扱われる。
 */
abstract class ExportCsvAbstract
{
  abstract public static function postType(): string;

  abstract protected function header(): array;

  abstract protected function data(): iterable;

  public static function isAllowed(): bool
  {
    return true;
  }

  final public static function exportParam(): string
  {
    return 'csv_export';
  }

  final public static function dryRunParam(): string
  {
    return 'dry_run';
  }

  final public function toArray(): array
  {
    return [$this->header(), ...$this->data()];
  }

  final public function handle(): void
  {
    try {
      if (isset($_GET[static::dryRunParam()])) {
        Html::table($this->toArray());
        exit;
      }

      while (ob_get_level() > 0) {
        ob_end_clean();
      }

      $filename = 'export_' . static::postType() . '_' . date('YmdHis');

      header('Content-Type: text/csv; charset=UTF-8');
      header("Content-Disposition: attachment; filename={$filename}.csv");

      (new CsvWriter(withBom: true))->execute(
        (function () {
          yield $this->header();
          yield from $this->data();
        })()
      );

      exit;
    } catch (\Throwable $throwable) {
      AppError::abort($throwable);
    }
  }

  final protected function getTermSlugs(int $postId, string $taxonomy): string
  {
    return (new ExportGetTermSlugsAction())($postId, $taxonomy);
  }
}
