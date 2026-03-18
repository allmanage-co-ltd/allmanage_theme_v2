<?php

namespace App\Support;

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
     * ダウンロード時のファイル名
     *
     * - .csv は自動付与されるので不要
     * - 例: 'news', 'onsale'
     */
    abstract protected function filename(): string;

    /**
     * CSVのヘッダー行
     *
     * - 1次元配列で返す
     * - row_data() の各行と順番を合わせること
     * - 例: ['ID', 'タイトル', '公開日']
     */
    abstract protected function row_header(): array;

    /**
     * CSVのデータ行
     *
     * - 2次元配列で返す（1要素が1行）
     * - row_headers() のカラム順と合わせること
     * - 例: [[$post->ID, $post->post_title, $post->post_date], ...]
     */
    abstract protected function row_data(): array;

    /**
     * デバッグの有効化
     */
    abstract protected function useDebug(): bool;

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
        if (!$this->useDebug()) {
            return;
        }

        d($this->toArray());
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
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename={$this->filename()}.csv");
        (new Csv(withBom: true))->write($this->rows());
        exit;
    }

    /**
     * ヘッダー行とデータ行を結合して返す
     *
     * - 1行目: row_headers() の返り値
     * - 2行目以降: row_data() の返り値をスプレッド展開
     */
    private function rows(): array
    {
        return [$this->row_header(), ...$this->row_data()];
    }
}
