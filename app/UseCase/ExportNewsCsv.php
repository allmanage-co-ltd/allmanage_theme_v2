<?php

namespace App\UseCase;

use App\CMS\Wrapper\MyWpQuery;
use App\Support\Csv;

/**---------------------------------------------
 * News CSVエクスポート
 * ---------------------------------------------
 * - 管理画面から news 投稿一覧を CSV でダウンロードする
 * - 他の投稿タイプに使う場合はこのファイルをコピーして
 *   クラス名・filename・row_header・rows を変更してください
 *
 * ■呼び出し例
 *   $export = new ExportNewsCsv();
 *   $export();
 */
final class ExportNewsCsv
{
    // ダウンロード時のファイル名（.csv は自動付与）
    private readonly string $filename;

    public function __construct()
    {
        $this->filename = 'news';
    }

    /**
     * CSVをダウンロード出力する
     *
     * - Content-Type と Content-Disposition を設定してブラウザにダウンロードさせる
     * - BOM付きUTF-8で出力するのでExcelでも文字化けしない
     * - write() 後は必ず exit すること（WordPress の後続処理を止めるため）
     */
    public function __invoke(): void
    {
        $rows = $this->rows();

        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename={$this->filename}.csv");

        (new Csv(withBom: true))->write($rows);
        exit;
    }

    /**
     * CSVのヘッダー行
     *
     * - 1次元配列で返す
     * - 出力したいカラムに合わせて変更する
     */
    private function row_headers(): array
    {
        return [
            'ID',
            'タイトル',
            '本文',
            '公開日'
        ];
    }

    /**
     * CSVの全行データを組み立ててる
     *
     * - 1行目はヘッダー行（row_headers() の返り値）
     * - 2行目以降は投稿データ（WP_Query で取得した投稿を1行1投稿で追加）
     *
     * ※ row_headers() のカラム順と foreach 内の配列の順番を必ず合わせること
     *   例: row_headers() が ['ID', 'タイトル', '公開日'] なら
     *       $rows[] = [$post->ID, $post->post_title, $post->post_date]
     *
     * ※ ACFフィールドを出力する場合は get_field() を使う
     *   例: $rows[] = [$post->ID, get_field('my_field', $post->ID)]
     */
    private function rows(): array
    {
        $rows = [$this->row_headers()];

        $query = MyWpQuery::new()
            ->setPostType('news')
            ->setPerPage(-1)
            ->setOrderByDate()
            ->build();

        foreach ($query->posts as $post) {
            $rows[] = [
                $post->ID,
                $post->post_title,
                $post->post_content,
                $post->post_date,
            ];
        }

        return $rows;
    }
}
