<?php

namespace App\UseCase\Csv\Export;

use App\Support\Csv;
use RuntimeException;

/**---------------------------------------------
 * CSVエクスポート 基底クラス
 * ---------------------------------------------
 * - CSVダウンロードの共通処理を提供する抽象クラス
 * - ファイル名・ヘッダー行・データ行はサブクラスに委ねる
 * - ダウンロード出力と配列取得の2つの呼び出し方に対応する
 */
abstract class AbstractExportCsv
{
    /**
     * 実行権限を持つユーザーロールを指定
     */
    protected function auth(): bool
    {
        return true;
    }

    /**
     * ダウンロード時のファイル名
     *
     * - .csv は自動付与されるので不要
     * - 例: 'news', 'onsale'
     */
    abstract protected function postType(): string;

    /**
     * CSVのヘッダー行
     *
     * - 1次元配列で返す
     * - data() の各行と順番を合わせること
     * - 例: ['ID', 'タイトル', '公開日']
     */
    abstract protected function header(): array;

    /**
     * CSVのデータ行
     *
     * - 2次元配列で返す（1要素が1行）
     * - header() のカラム順と合わせること
     * - 例: [[$post->ID, $post->post_title, $post->post_date], ...]
     */
    abstract protected function data(): iterable;

    /**
     * 配列で取得
     *
     * - デバッグや画面プレビュー用
     * - ヘッダー行 + データ行の2次元配列を返す
     */
    public function toArray(): array
    {
        return $this->rows();
    }

    /**
     * デバッグ用
     */
    public function debug(): void
    {
        echo '<pre style="margin-top: 150px;">';
        d($this->toArray());
        echo '</pre>';
        exit;
    }

    /**
     * CSVをダウンロード出力する
     *
     * - Content-Type と Content-Disposition を設定してブラウザにダウンロードさせる
     * - BOM付きUTF-8で出力するのでExcelでも文字化けしない
     * - write() 後は必ず exit すること（WordPress の後続処理を止めるため）
     */
    public function handle(): void
    {
        try {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $export_file_name = 'export_' . $this->postType() . '_' . date('YmdHis', time());

            header('Content-Type: text/csv; charset=UTF-8');
            header("Content-Disposition: attachment; filename={$export_file_name}.csv");

            (new Csv(withBom: true))->write($this->stream());

            exit;
        } catch (RuntimeException $e) {
            wp_die($e->getMessage());
        }
    }

    /**
     * ヘッダー行とデータ行を結合して返す
     *
     * - 1行目: headers() の返り値
     * - 2行目以降: data() の返り値をスプレッド展開
     */
    private function rows(): array
    {
        return [$this->header(), ...$this->data()];
    }

    /**
     * CSV出力用のストリームを生成する
     *
     * - 1行目に header() の返り値を出力
     * - 2行目以降に data() の各行を1件ずつ出力
     * - 配列に展開せず逐次処理することでメモリ使用量を抑える
     */
    private function stream(): iterable
    {
        yield $this->header();

        foreach ($this->data() as $row) {
            yield $row;
        }
    }

    /**
     * エクスポートキーを取得（外部公開用）
     *
     * - URLクエリ (?csv_export=xxx) と一致判定に使用する
     */
    public function key(): string
    {
        return $this->postType();
    }

    /**
     * 実行可能か判定（外部公開用）
     */
    public function can(): bool
    {
        return $this->auth();
    }
}
