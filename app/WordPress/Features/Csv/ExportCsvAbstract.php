<?php

namespace App\WordPress\Csv\Abstracts;

use App\Errors\AppError;
use App\Shared\Html;
use App\Shared\CsvWriter;
use App\WordPress\Csv\Actions\ExportGetTermSlugsAction;

/**
 * CSV エクスポートの共通処理。
 *
 * ファイル名や出力まわりの流れはここで揃え、案件ごとの差分だけを子クラスに残す。
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

    final protected function getTermSlugs(int $postId, string $taxonomy): string
    {
        return (new ExportGetTermSlugsAction())($postId, $taxonomy);
    }

    final public function handle(): void
    {
        if (isset($_GET[$this->dryRunParam()])) {
            Html::table($this->toArray());
            exit;
        }

        try {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $filename = 'export_' . $this->postType() . '_' . date('YmdHis');

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
            AppError::abort($throwable->getMessage());
        }
    }
}
