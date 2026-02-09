<?php

namespace App\Plugins;

/**---------------------------------------------
 * Welcart 連携クラス
 * ---------------------------------------------
 * - Welcart（USCES）専用の拡張処理をまとめる
 * - 商品検索・管理画面・カート表示などのカスタマイズを担当
 */
class Welcart extends Plugin
{
  public function __construct()
  {
    if (!class_exists('usc_e_shop'))
      return;
  }

  /**
   * フック登録
   */
  public function boot(): void
  {
    // add_filter('posts_search', 'searchInItemCode', 10, 2);

    // add_action('admin_menu', 'AddOriginSubmenuAdminView');
    // add_filter('usces_filter_backCustomer_page', 'filterBackCustomerPage', 10, 1);
    // add_filter('usces_filter_cart_prebutton', 'changeCartPrebuttonUrl');

    // add_filter('usces_filter_cart_rows', 'myFilterCartRows', 10, 2);
    // add_filter('usces_filter_confirm_rows', 'myFilterCartRows', 10, 2);

    //
    // add_filter('usces_filter_after_zipcode', 'removeAllExamples', 10, 2);
    // add_filter('usces_filter_after_address1', 'removeAllExamples', 10, 2);
    // add_filter('usces_filter_after_address2', 'removeAllExamples', 10, 2);
    // add_filter('usces_filter_after_address3', 'removeAllExamples', 10, 2);
    // add_filter('usces_filter_after_tel', 'removeAllExamples', 10, 2);
    // add_filter('usces_filter_after_fax', 'removeAllExamples', 10, 2);

    // add_filter('usces_filter_cart_rows', 'myFilterCartRows', 10, 2);
    // add_filter('usces_filter_confirm_rows', 'myFilterCartRows', 10, 2);
  }

  /**
   * 商品情報をまとめて取得
   */
  public static function item()
  {
    return [
      '' => '',
    ];
  }

  /**
   * カテゴリ編集画面への導線を追加する
   */
  public function AddOriginSubmenuAdminView()
  {
    add_submenu_page(
      'usc-e-shop/usc-e-shop.php',
      'カテゴリ',
      'カテゴリ',
      'manage_categories',
      'edit-tags.php?taxonomy=category',
      ''
    );
  }

  /**
   * 商品コードを検索クエリに追加
   */
  public function searchInItemCode($search, $wp_query)
  {
    global $wpdb;
    if (!$wp_query->is_search)
      return $search;

    if (!isset($wp_query->query_vars))
      return $search;

    if (isset($wp_query->query_vars['s'])) {
      $search_words = explode(' ', $wp_query->query_vars['s']);
      if (count($search_words) > 0) {
        $search = '';
        foreach ($search_words as $word) {
          if (!empty($word)) {
            $search_word  = '%' . esc_sql($word) . '%';
            $search      .= " AND (
            {$wpdb->posts}.post_title LIKE '{$search_word}'
            OR {$wpdb->posts}.post_content LIKE '{$search_word}'
            OR {$wpdb->posts}.ID IN (
              SELECT distinct post_id
              FROM {$wpdb->postmeta}
              WHERE {$wpdb->postmeta}.meta_key IN ('_itemCode') AND meta_value LIKE '{$search_word}'
            )
          ) ";
          }
        }
      }
    }
    return $search;
  }

  /**
   * カート戻りボタンの遷移先を変更
   */
  public function changeCartPrebuttonUrl()
  {
    $url = home_url() . '/category/item';
    return ' onclick="location.href=\'' . $url . '/\'"';
  }

  /**
   * カート／確認画面の行表示調整
   */
  // function filterBackCustomerPage($page)
  // {
  //   if (usces_is_login()) {
  //     $page = 'cart';
  //   }
  //   return $page;
  // }

  /**
   * 入力フォーム内のサンプル文言を削除
   */
  public function filterCartRows($html, $cart)
  {
    $html = preg_replace('/<td class="num">.*?<\/td>/s', '', $html); // No.を削除
    $html = preg_replace('/<td class="aright unitprice" data-label="単価">.*?<\/td>/s', '', $html); // 単価を削除
    $html = preg_replace('/<td class="unitprice" data-label="単価">.*?<\/td>/s', '', $html);        // 単価を削除
    $html = preg_replace('/<td class="stock" data-label="在庫状態">.*?<\/td>/s', '', $html);         // 在庫状態を削除
    return $html;
  }

  /**
   *
   */
  public function removeAllExamples($ex, $applyform)
  {
    return '';
  }
}