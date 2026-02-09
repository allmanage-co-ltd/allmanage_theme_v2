<?php

namespace App\Services\CSV;

use App\Services\Config;

/**---------------------------------------------
 * カスタム投稿CSVエクスポーター
 * ---------------------------------------------
 *
 * 作成中
 *
 */
class Expoter extends CSV
{
  public function __construct()
  {
    //
  }

  /**
   *
   */
  public function export($headers, $row)
  {
    header('Content-Type: text/csv; charset=Shift_JIS');
    header('Content-Disposition: attachment; filename="' . Config::get('csv.export_file_prefix') . date('Ymd_His') . '.csv"');

    $output  = fopen('php://output', 'w');
    $headers = [
      //
    ];
    fputs($output, mb_convert_encoding(implode(',', $headers) . "\n", 'SJIS-win', 'UTF-8'));

    $posts = get_posts([
      // 'post_type' => 'post',
      'posts_per_page' => -1,
      'post_status'    => 'publish',
    ]);

    foreach ($posts as $post) {
      $row = [
        //
      ];

      fputs($output, mb_convert_encoding(implode(',', $row) . "\n", 'SJIS-win', 'UTF-8'));
    }
    fclose($output);
    exit;
  }
}