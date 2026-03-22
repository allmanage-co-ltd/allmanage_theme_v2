<?php

namespace App\Plugins;

/**---------------------------------------------
 * Advanced Custom Fields 連携クラス
 * ---------------------------------------------
 * - テンプレートにメソッドを提供したい場合はここに書く
 */
class Acf
{
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
