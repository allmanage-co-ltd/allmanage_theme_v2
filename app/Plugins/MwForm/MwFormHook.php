<?php

namespace App\Plugins\MwForm;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * MW WP Form 連携クラス
 * ---------------------------------------------
 * - MW WP Form プラグイン専用の拡張処理をまとめる
 * - フォームの挙動・メール・初期設定・表示調整を一元管理
 * - テーマやテンプレートに MW WP Form のロジックを漏らさない
 */
class MwFormHook implements BootableWpHookInterface
{
    /**
     * 初期化処理
     */
    public function boot(): void
    {
        if (!class_exists('MW_WP_Form')) {
            return;
        }

        $mwform = new MwForm();

        // $contact_form_id = 1;
        // add_filter("mwform_validation_mw-wp-form-{$contact_form_id}", $mwform->validation(...), 10, 3);
        // add_filter("mwform_admin_mail_mw-wp-form-{$contact_form_id}", $mwform->entryAutobackMyMail(...), 10, 3);

        add_filter('mwform_default_content', $mwform->defaultContent(...));
        add_filter('mwform_default_settings', $mwform->defaultSettings(...), 10, 2);
        add_filter('mwform_custom_mail_tag', $mwform->tag(...), 10, 3);
        add_action('wp_footer', $mwform->footerScript(...), 9999);
        add_filter('user_can_richedit', $mwform->disableVisualEditor(...));
        add_action('load-post.php', $mwform->disableVisualEditor(...));
        add_action('load-post-new.php', $mwform->disableVisualEditor(...));
    }
}
