<?php

namespace App\UseCase\Csv\Export;

use App\CMS\Wrapper\MyWpQuery;
use App\UseCase\GetAcfFields;

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
 *   <a href="?csv_export=news">CSVダウンロード</a>
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
     * 実行権限を持つユーザーロールを指定
     *
     * manage_options    : 管理者のみ
     * edit_posts        : 編集者のみ
     * edit_others_posts : 編集者以上
     */
    protected function auth(): bool
    {
        return current_user_can('edit_others_posts');
    }

    /**
     * カスタム投稿のスラッグを指定
     *
     * - Hook側で key() を通じて参照される
     * - ?csv_export=news の "news" に対応する
     * - ファイル名のベースとして使用される
     */
    protected function postType(): string
    {
        // URL : ?csv_export=news
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
            'post_id',
            'post_status',
            'post_title',
            'post_content',
            'post_date',
            'post_thumbnail',
            'news_cat',
            'acf_is_public',
            'acf_price',
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
            error_log(print_r(get_the_terms($post->ID, 'news_cat'), true));
            yield [
                $post->ID,
                $post->post_status,
                $post->post_title,
                $post->post_content,
                get_the_date('Y-m-d H:i:s', $post),
                get_the_post_thumbnail_url($post->ID),
                $this->getTermSlugs($post->ID, 'news_cat'),
                $acf['acf_is_public'] ?? '',
                $acf['acf_price'] ?? '',
            ];
        }
    }


    private function getTermSlugs(int $post_id, string $taxonomy): string
    {
        if (!taxonomy_exists($taxonomy)) {
            return '';
        }

        $terms = get_the_terms($post_id, $taxonomy);

        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        return implode(',', array_map(fn($t) => $t->slug ?? '', $terms));
    }
}
