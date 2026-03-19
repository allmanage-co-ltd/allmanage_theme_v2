<?php

namespace App\Packages\Csv\Actions;

use App\Packages\Csv\Enums\ImportColumnActionEnum;

/**---------------------------------------------
 * 投稿の作成・更新
 * ---------------------------------------------
 * map() で SAVE_POST が指定されたカラムを対象に
 * wp_insert_post / wp_update_post を実行する invokable クラス。
 *
 * - post_id が存在すれば更新、存在しなければ新規作成
 * - isDryRun 時は保存を行わず 0 を返す
 *
 * 日時フィールド（post_date / post_date_gmt）の正規化:
 *   CSVの日時は "2025/12/3 15:50:00" などスラッシュ区切りや
 *   ゼロ埋めなしの形式になりがちなため、WordPress が求める
 *   "Y-m-d H:i:s" 形式に自動変換する。
 */
class ImportPostSaveAction
{
    public function __construct(
        private readonly string $postType,
        private readonly array  $map,
        private readonly bool   $isDryRun,
    ) {
        //
    }

    /**
     * 投稿を保存する
     *
     * - map() の SAVE_POST カラムのみ対象とする
     * - post_id が空の場合は新規作成
     * - isDryRun が true の場合は何もせず 0 を返す
     */
    public function __invoke(array $row): int
    {
        $data = ['post_type' => $this->postType];

        foreach ($this->map as $key => $config) {

            if (($config['action'] ?? null) !== ImportColumnActionEnum::SavePost) {
                continue;
            }

            $value = trim((string) ($row[$key] ?? ''));

            if ($value === '') {
                continue;
            }

            $mappedKey        = $key === 'post_id' ? 'ID' : $key;
            $data[$mappedKey] = $this->normalizeValue($mappedKey, $value);
        }

        $post_id = (int) ($data['ID'] ?? 0);

        if ($this->isDryRun) {
            return 0;
        }

        $result = $post_id > 0
            ? wp_update_post($data, true)
            : wp_insert_post($data, true);

        if (is_wp_error($result)) {
            throw new \RuntimeException($result->get_error_message());
        }

        return (int) $result;
    }

    /**
     * フィールドに応じた値の正規化
     *
     * - post_date / post_date_gmt:
     *     "2025/12/3 15:50:00" など様々な日時文字列を
     *     WordPress が求める "Y-m-d H:i:s" 形式に変換する。
     *     strtotime() で解析できない値はそのまま返す（WordPressに任せる）。
     * - それ以外のフィールド: 変換なしでそのまま返す
     */
    private function normalizeValue(string $key, string $value): string
    {
        if (!in_array($key, ['post_date', 'post_date_gmt'], true)) {
            return $value;
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return $value;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
