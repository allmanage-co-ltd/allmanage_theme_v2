<?php

namespace App\Packages\Csv\Abstracts;

use App\Packages\Csv\Actions\ExportGetTermSlugsAction;
use App\Packages\Csv\Infrastructure\CsvWriter;
use App\Support\Html;

/**---------------------------------------------
 * CSVエクスポート 基底クラス
 * ---------------------------------------------
 * CSVダウンロードの共通処理を提供する抽象クラス。
 * ファイル名・ヘッダー行・データ行はサブクラスに委ねる。
 *
 * ---------------------------------------------
 * ■ サブクラスで定義するメソッド
 * ---------------------------------------------
 * 必須:
 *   postType()   … 投稿タイプ（ルーティング・ファイル名に使用）
 *   header()     … CSVヘッダー行（1次元配列）
 *   data()       … CSVデータ行（iterable / Generator）
 *
 * 省略可能（デフォルト値あり）:
 *   isAllowed()  … 実行権限（デフォルト: manage_options）
 */
abstract class ExportCsv
{
    /**
     * 投稿タイプ
     *
     * - ルーティングキー・ファイル名ベースとして使用される
     * - 例: 'news' → ?csv_export=news, export_news_20260101.csv
     */
    abstract public static function postType(): string;

    /**
     * CSVのヘッダー行
     *
     * - 1次元配列で返す
     * - data() の各行と順番を合わせること
     */
    abstract protected function header(): array;

    /**
     * CSVのデータ行
     *
     * - iterable（配列 or Generator）で返す
     * - yield を使うとメモリ効率がよい
     */
    abstract protected function data(): iterable;

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
    final public static function exportParam(): string
    {
        return 'csv_export';
    }

    /**
     * dryRun クエリパラメータ名（?dry_run=1 で有効）
     */
    final public static function dryRunParam(): string
    {
        return 'dry_run';
    }

    /**
     * CSVデータを配列で取得（プレビュー・テスト用）
     */
    final public function toArray(): array
    {
        return [$this->header(), ...$this->data()];
    }

    /**
     * タクソノミーのスラッグをカンマ区切り文字列で返すメソッドを継承クラスへ中継
     *
     * - Wordpress依存かつ取得アクションなため
     */
    final protected function getTermSlugs(int $post_id, string $taxonomy): string
    {
        return (new ExportGetTermSlugsAction())($post_id, $taxonomy);
    }

    /**
     * CSVをダウンロード出力する
     *
     * - ?dry_run=1 が付いている場合はデータをプレビュー表示して終了する
     */
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
        } catch (\RuntimeException $e) {
            wp_die($e->getMessage());
        }
    }
}
