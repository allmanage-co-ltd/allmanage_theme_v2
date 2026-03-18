<?php

namespace App\UseCase;

use App\CMS\Wrapper\MyWpQuery;
use App\Support\AbstractExportCsv;

/**---------------------------------------------
 * News CSVエクスポート
 * ---------------------------------------------
 * このクラスはNewsカスタム投稿を題材にした実装見本です。
 * 他の投稿タイプの実装はこのファイルを複製して各種適切に変更してください
 *
 * ---------------------------------------------
 * ■ エクスポート実行方法
 * ---------------------------------------------
 * URLアクセス: config/csv.phpへのクラス文字列登録必須
 *
 *   ?export=news
 *
 * ※ config/csv.php の 'exporter' に必ずクラス文字列を登録してください。
 *
 *   'exporter' => [
 *       \App\UseCase\ExportNewsCsv::class,
 *   ]
 *
 * → exporterにクラスを登録するとHook側でクエリパラメータを判定し、
 *   filename() と一致する エクスポートクラスの handle() が実行される
 *
 * ---------------------------------------------
 * ■ 直接実行（開発・デバッグ用）
 * ---------------------------------------------
 * app ディレクトリ内から呼ぶ場合:
 *
 *   use App\UseCase\ExportNewsCsv;
 *
 *   $exporter = new ExportNewsCsv();
 *   $exporter->handle();   // 関数でCSVダウンロード実行
 *   $exporter->toArray();  // 配列でデータ取得
 *   $exporter->debug();    // デバッグ
 *
 * ---------------------------------------------
 * ■ テンプレートから実行する場合
 * ---------------------------------------------
 * bootstrap/functions.php に中間関数を定義:
 *
 *   function news_csv_exporter(): \App\UseCase\ExportNewsCsv
 *   {
 *       return new \App\UseCase\ExportNewsCsv();
 *   }
 *
 * テンプレート側:
 *
 *   <?php news_csv_exporter()->handle(); ?>
 *
 * ※ handle() 実行後は exit されるため、それ以降のHTMLは出力されない
 */
final class ExportNewsCsv extends AbstractExportCsv
{
    /**
     * カスタム投稿のスラッグを指定
     *
     * - Hook側で key() を通じて参照される
     * - ?export=news の "news" に対応する
     * - ファイル名のベースとして使用される
     */
    protected function postType(): string
    {
        // URL : ?export=news
        // FILE: export_news_20260318124530.csv
        return 'news';
    }

    /**
     * CSVのヘッダー行
     *
     * - data() の配列順と合わせること
     */
    protected function header(): array
    {
        return [
            'ID',
            'タイトル',
            '本文',
            '公開日',
            '公開状態'
        ];
    }

    /**
     * CSVのデータ行
     *
     * - news 投稿を全件取得して逐次返却する
     * - yield を使用することでメモリ使用量を抑える
     * - ACF含めカスタムフィールドも取得可能
     */
    protected function data(): iterable
    {
        $query = MyWpQuery::new()
            ->setPostType('news')
            ->setPerPage(-1)
            ->setOrderByDate()
            ->build();

        foreach ($query->posts as $post) {
            $acf = (new GetAcfFields($post->ID))->news();

            yield [
                $post->ID,
                $post->post_title,
                $post->post_content,
                get_the_date('Y-m-d H:i:s', $post),
                $acf['acf_is_public'] ?? '',
            ];
        }
    }
}
