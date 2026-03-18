<?php

namespace App\UseCase;

use App\CMS\Wrapper\MyWpQuery;
use App\Support\AbstractExportCsv;

/**---------------------------------------------
 * News CSVエクスポート
 * ---------------------------------------------
 * ※ このクラスはサンプルです。他の投稿タイプで使う場合は
 *    このファイルをコピーして以下を変更してください
 *    - ファイル名・クラス名: Export{投稿タイプ名}Csv.php
 *    - filename()・row_header()・row_data() の中身
 *
 * ---------------------------------------------
 * ■ 使い方（ExportNewsCsvの場合）
 * ---------------------------------------------
 * app ディレクトリ内から呼ぶ場合:
 *
 *   use App\UseCase\ExportNewsCsv;
 *
 *   $exporter = new ExportNewsCsv();
 *   $exporter->handle();   // ダウンロード
 *   $exporter->toArray();  // 配列で取得
 *   $exporter->debug();    // デバッグ（useDebug() が true の場合のみ）
 *
 * テンプレートから呼ぶ場合は名前空間の関係で中間関数が必要です
 * bootstrap/functions.php に定義してください:
 *
 *   function news_csv_exporter(): \App\UseCase\ExportNewsCsv
 *   {
 *       return new \App\UseCase\ExportNewsCsv();
 *   }
 *
 * テンプレート側:
 *
 *   <?php news_csv_exporter()->handle(); ?>   // ダウンロード
 *   <?php news_csv_exporter()->toArray(); ?>  // 配列で取得
 *   <?php news_csv_exporter()->debug(); ?>    // デバッグ（useDebug() が true の場合のみ）
 */
final class ExportNewsCsv extends AbstractExportCsv
{
    /**
     * ダウンロード時のファイル名
     */
    protected function filename(): string
    {
        return 'news';
    }

    /**
     * CSVのヘッダー行
     * - row_data() の配列順と合わせること
     */
    protected function row_header(): array
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
     * - news 投稿を全件取得して2次元配列で返す
     * - ACFフィールドを出力する場合は get_field('field_key', $post->ID) を使う
     */
    protected function row_data(): array
    {
        $query = MyWpQuery::new()
            ->setPostType('news')
            ->setPerPage(-1)
            ->setOrderByDate()
            ->build();

        $rows = [];
        foreach ($query->posts as $post) {
            $acf    = (new GetAcfFields($post->ID))->news();
            $rows[] = [
                $post->ID,
                $post->post_title,
                $post->post_content,
                $post->post_date,
                $acf['acf_is_public'],
            ];
        }
        return $rows;
    }

    /**
     * デバッグの有効化
     *
     * trueの場合、中身の配列をデバッグ表示可能なdebug()メソッドが呼べるようになる
     * デバッグのタイミング以外は念のため必ずfalseにすること
     */
    protected function useDebug(): bool
    {
        return false;
    }
}
