<?php

namespace App\Services\Csv\Actions;

/**---------------------------------------------
 * 添付ファイルID解決
 * ---------------------------------------------
 * URLから WordPress の attachment_id を解決する invokable クラス。
 * ImportValueConvertAction（Gallery型）・ImportColumnAction（SetThumbnail）・
 * ImportRunAction（dryRunログ）から共用される。
 *
 * 解決優先順位:
 *   1. attachment_url_to_postid() による完全URL一致
 *   2. _wp_attached_file メタによる相対パス一致（同ドメイン前提）
 *   3. ファイル名による後方一致（ドメイン違い・環境移行時に有効）
 */
class ImportAttachmentResolveAction
{
  /**
   * URLから attachment_id を解決して返す
   *
   * - 空文字の場合は 0 を返す
   * - すべての方法で解決できない場合は 0 を返す
   */
  public function __invoke(string $url): int
  {
    $url = \trim($url);

    if ($url === '') {
      return 0;
    }

    // 1. 完全URL一致（最速・最確実）
    $id = attachment_url_to_postid($url);

    if ($id) {
      return $id;
    }

    global $wpdb;
    /** @var \wpdb $wpdb */

    // 2. 相対パス一致（同ドメイン時の環境差吸収）
    $upload   = wp_get_upload_dir();
    $relative = \str_replace($upload['baseurl'] . '/', '', $url);

    if ($relative !== $url) {
      $id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
                 WHERE meta_key = '_wp_attached_file'
                 AND meta_value = %s
                 LIMIT 1",
        $relative
      ));

      if ($id) {
        return $id;
      }
    }

    // 3. ファイル名後方一致（ドメイン違い・環境移行時の救済）
    //    _wp_attached_file の値は "2026/02/filename.png" 形式で保存される。
    //    URLの末尾ファイル名で LIKE 検索することで、異なるドメインや
    //    ディレクトリ構成が変わっていても一致させることができる。
    $filename = \basename($url);

    $id = (int) $wpdb->get_var($wpdb->prepare(
      "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_wp_attached_file'
             AND meta_value LIKE %s
             LIMIT 1",
      '%/' . $wpdb->esc_like($filename)
    ));

    return $id ?: 0;
  }
}
