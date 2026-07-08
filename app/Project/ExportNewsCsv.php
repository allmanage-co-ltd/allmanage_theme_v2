<?php

namespace App\Project;

use App\Enums\CsvColumnEnum;
use App\Services\Query\MyWpQuery;
use App\Services\Csv\ExportCsvAbstract;

/**---------------------------------------------
 * News CSVエクスポート
 * ---------------------------------------------
 * このクラスはNewsカスタム投稿を題材にした実装見本です。
 * 他の投稿タイプの実装はこのファイルを複製して各種適切に変更してください。
 *
 * ---------------------------------------------
 * ■ 注意事項
 * ---------------------------------------------
 * CSVオプションページの「投稿タイプ」選択肢へ反映するためには
 * config/cms.php の exporter にクラス文字列を追加する必要があります。
 *
 * 'exporter'   => [
 *     \App\Project\ExportNewsCsv::class,
 * ],
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
final class ExportNewsCsv extends ExportCsvAbstract
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
      CsvColumnEnum::PostId->value,
      CsvColumnEnum::PostStatus->value,
      CsvColumnEnum::PostTitle->value,
      CsvColumnEnum::PostContent->value,
      CsvColumnEnum::PostDate->value,
      CsvColumnEnum::PostThumbnail->value,
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
      ->setPostType(self::postType())
      ->setPostStatus('any')
      ->setPerPage(-1)
      ->setOrderByDate()
      ->build();

    foreach ($query->posts as $post) {
      yield [
        $post->ID,
        $post->post_status,
        $post->post_title,
        $post->post_content,
        get_the_date('Y-m-d H:i:s', $post),
        get_the_post_thumbnail_url($post->ID),
        $this->getTermSlugs($post->ID, 'news_cat'),
        get_post_meta($post->ID, 'acf_is_public', true),
        get_post_meta($post->ID, 'acf_price', true),
        get_post_meta($post->ID, 'acf_check', true),
      ];
    }
  }
}
