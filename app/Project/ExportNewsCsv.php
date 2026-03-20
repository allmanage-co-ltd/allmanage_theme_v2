<?php

namespace App\UseCase\Csv\Export;

use App\CMS\Wrapper\MyWpQuery;
use App\Packages\Csv\Abstracts\ExportCsv;

/**---------------------------------------------
 * News CSVエクスポート
 * ---------------------------------------------
 * このクラスはNewsカスタム投稿を題材にした実装見本です。
 * 他の投稿タイプの実装はこのファイルを複製して各種適切に変更してください。
 *
 * ---------------------------------------------
 * ■ エクスポート実行方法
 * ---------------------------------------------
 *   ?csv_export=news           ← CSVダウンロード
 *   ?csv_export=news&dry_run=1 ← データをプレビュー表示（ダウンロードなし）
 *
 * ---------------------------------------------
 * ■ 直接実行（開発・デバッグ用）
 * ---------------------------------------------
 *   $exporter = new ExportNewsCsv();
 *   $exporter->handle();   // CSVダウンロード実行
 *   $exporter->toArray();  // 配列でデータ取得
 */
final class ExportNewsCsv extends ExportCsv
{
    /**
     * 実行を許可するユーザー権限、もしくは条件式
     * 実行前に検証し、true の場合のみ実行可能
     *
     * - デフォルト: 管理者のみ
     * - is_admin()等、権限以外でも指定可能
     */
    public static function isAllowed(): bool
    {
        return current_user_can('edit_others_posts');
    }

    /**
     * 投稿タイプのスラッグ
     *
     * - ?csv_export=news の "news" に対応する
     */
    public static function postType(): string
    {
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
            'acf_check',
        ];
    }

    /**
     * CSVのデータ行
     *
     * - news 投稿を全件取得して逐次返却する
     * - yield を使用することでメモリ使用量を抑える
     */
    protected function data(): iterable
    {
        $query = MyWpQuery::new()
            ->setPostType('news')
            ->setPerPage(-1)
            ->setOrderByDate()
            ->build();

        foreach ($query->posts as $post) {
            $acf = get_fields($post->ID);
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
                $acf['acf_check'] ?? '',
            ];
        }
    }
}
