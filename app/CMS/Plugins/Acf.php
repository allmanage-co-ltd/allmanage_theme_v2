<?php

namespace App\CMS\Plugins;

/**---------------------------------------------
 * Advanced Custom Fields 連携クラス
 * ---------------------------------------------
 *
 */
class Acf extends Plugin
{
    public function __construct()
    {
        if (!class_exists('ACF') || !class_exists('acf_pro')) {
            return;
        }
    }

    /**
     * 初期化処理
     */
    #[\Override]
    public function boot(): void
    {
        // add_action('acf/init', [$this, 'registerOptionPage']);
    }

    /**
     * オプションページ登録
     *
     * - 必要な時だけ使用する
     */
    public function registerOptionPage(): void
    {
        // if (function_exists('acf_add_options_page')) {
        //     acf_add_options_page([
        //         'page_title' => 'テーマ設定',
        //         'menu_title' => 'テーマ設定',
        //         'menu_slug'  => 'theme-settings',
        //         'capability' => 'manage_options',
        //         'redirect'   => false,
        //     ]);
        // }
    }

    /**
     * ACF存在確認
     */
    public static function isActive(): bool
    {
        return function_exists('get_fields');
    }

    /**
     * キーからフィールドを一括取得して返却、なければ空配列
     */
    public static function getByKeys(int $post_id, array $keys): array
    {
        if (!self::isActive()) {
            return [];
        }

        static $cache = [];
        if (!isset($cache[$post_id])) {
            $cache[$post_id] = get_fields($post_id) ?: [];
        }

        $fields  = $cache[$post_id];
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $fields[$key] ?? null;
        }

        return $results;
    }
}
