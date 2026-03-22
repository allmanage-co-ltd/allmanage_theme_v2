<?php
return [
    /**
     *  検索フィルター機能の有効化
     *
     * - true の場合、add_filter を使って検索条件を拡張する
     * - false の場合、標準のWordPress検索を使用する
     */
    'use_add_filter' => true,

    /**
     * 投稿タイプごとの検索フィルター設定
     *
     * 設定項目:
     *   add_taxonomies : タクソノミーも検索対象に含める
     *   add_meta_keys  : 指定したカスタムフィールドを検索対象に含める
     */
    'filter' => [

        'news' => [
            'add_taxonomies' => true,

            'add_meta_keys' => [
                // 'acf_is_public',
                // 'acf_check',
                'acf_price',
            ],
        ],
    ]
];
