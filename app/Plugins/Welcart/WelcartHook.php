<?php

namespace App\Plugins\Welcart;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * Welcart 連携クラス
 * ---------------------------------------------
 * - Welcart（USCES）専用の拡張処理をまとめる
 * - 商品検索・管理画面・カート表示などのカスタマイズを担当
 */
class WelcartHook implements BootableWpHookInterface
{
    /**
     * フック登録
     */
    public function boot(): void
    {
        if (!class_exists('usc_e_shop')) {
            return;
        }

        // $welcart = new Welcart();

        // add_filter('posts_search', $welcart->searchInItemCode(...), 10, 2);
        // add_action('admin_menu', $welcart->AddOriginSubmenuAdminView(...));
        // add_filter('usces_filter_cart_prebutton', $welcart->changeCartPrebuttonUrl(...));
        // add_filter('usces_filter_backCustomer_page', $welcart->uscesFilterBackCustomerPage(...), 10, 1);
        // add_filter('usces_filter_cart_rows', $welcart->myFilterCartRows(...), 10, 2);
        // add_filter('usces_filter_confirm_rows', $welcart->myFilterCartRows(...), 10, 2);
        // add_action('wp_head', $welcart->showMemberEdit(...));
        // add_filter('usces_filter_after_zipcode', $welcart->removeAllExamples(...), 10, 2);
        // add_filter('usces_filter_after_address1', $welcart->removeAllExamples(...), 10, 2);
        // add_filter('usces_filter_after_address2', $welcart->removeAllExamples(...), 10, 2);
        // add_filter('usces_filter_after_address3', $welcart->removeAllExamples(...), 10, 2);
        // add_filter('usces_filter_after_tel', $welcart->removeAllExamples(...), 10, 2);
        // add_filter('usces_filter_after_fax', $welcart->removeAllExamples(...), 10, 2);
        // add_filter('usces_filter_cart_rows', $welcart->myFilterCartRows(...), 10, 2);
        // add_filter('usces_filter_confirm_rows', $welcart->myFilterCartRows(...), 10, 2);
    }
}
