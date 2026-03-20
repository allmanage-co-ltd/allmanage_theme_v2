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
     *
     * - get_fields() で取得を試みる（DBアクセス1回）
     * - get_fields() にキーが存在しない場合は get_post_meta() で個別取得する
     *   （ACFフィールドグループのキー名と wp_postmeta のメタキーが一致しない場合の救済）
     */
    public static function getByKeys(int $post_id, array $keys): array
    {
        static $cache = [];
        if (!isset($cache[$post_id])) {
            $cache[$post_id] = (self::isActive() ? get_fields($post_id) : false) ?: [];
        }

        $fields  = $cache[$post_id];
        $results = [];

        foreach ($keys as $key) {
            $value = $fields[$key] ?? null;

            if ($value !== null && $value !== false) {
                // get_fields() に値がある場合はそのまま使う
                $results[$key] = $value;
            } else {
                // get_fields() が null / false の場合は直接メタから取得する。
                // - null: フィールドが登録されていない・未保存
                // - false: true_false 型のフィールドが「オフ」の状態で保存されている
                //   （ACF は true_false=off を boolean false で返すが、
                //     postmeta には '0' として保存されている）
                $meta          = get_post_meta($post_id, $key, true);
                $results[$key] = $meta !== '' ? $meta : null;
            }
        }

        return $results;
    }
}
