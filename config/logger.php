<?php

return [
    /**-----------------------------------
     * ログ設定
     * - use_xxx_log: ログの有効/無効
     * - xxx_log_dir: ログファイルの出力先（テーマルートからの相対パス）
     *----------------------------------*/

    // アプリログ
    'use_app_log'    => true,
    'app_log_dir'    => '/storage/logs/',

    // アクセスログ
    'use_access_log' => true,
    'access_log_dir' => '/storage/logs/access/',

    // エラーログ（不要？）
    // 'use_error_log'  => false,
    // 'error_log_dir'  => '/storage/logs/errors/',
];
