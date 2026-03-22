<?php

use App\Enums\LogFieeldEnum;

return [
    /**
     * アプリケーションログ設定
     *
     * - アプリ内部のログ出力用（任意ログ）
     * - Logger::info / error などから使用される想定
     *
     * use : 有効 / 無効
     * dir : ログ保存ディレクトリ（プロジェクトルート基準）
     */
    'app'    => [
        'use' => true,
        'dir' => '/storage/logs/app/',
    ],

    /**
     * アクセスログ設定
     *
     * - リクエスト単位のログを記録
     * - フロント / 管理画面の両方に対応
     *
     * use     : 有効 / 無効
     * dir     : ログ保存ディレクトリ
     * channel : ログチャンネル名（Logger識別用）
     * hooks   : フックトリガー（このタイミングでログ取得）
     * content : 出力するログ項目（Enumで定義）
     */
    'access' => [
        'use'     => true,
        'dir'     => '/storage/logs/access/',
        'channel' => 'access',

        // ログ取得のトリガーとなるWordPressフック
        'hooks'   => [
            'template_redirect',
            'admin_init',
        ],

        // 出力するログフィールド
        'content' => [
            LogFieeldEnum::requestId, // リクエストID
            LogFieeldEnum::ip,        // クライアントIP
            LogFieeldEnum::xff,       // X-Forwarded-For
            LogFieeldEnum::method,    // HTTPメソッド
            LogFieeldEnum::uri,       // リクエストURI
            LogFieeldEnum::query,     // クエリパラメータ
            LogFieeldEnum::referer,   // リファラ
            LogFieeldEnum::ua,        // ユーザーエージェント
            LogFieeldEnum::userId,    // ログインユーザーID
            LogFieeldEnum::postId,    // 投稿ID（該当する場合）
            LogFieeldEnum::postType,  // 投稿タイプ
            LogFieeldEnum::status,    // HTTPステータス
            LogFieeldEnum::is404,     // 404判定
        ],
    ],

    /**
     * エラーログ設定
     *
     * - 例外やエラー発生時のログ
     * - AppError::abort() などから使用される
     *
     * use     : 有効 / 無効
     * dir     : ログ保存ディレクトリ
     * content : 出力するログ項目
     */
    'error'  => [
        'use'     => true,
        'dir'     => '/storage/logs/errors/',

        // 出力するログフィールド
        'content' => [
            LogFieeldEnum::requestId, // リクエストID
            LogFieeldEnum::ip,        // クライアントIP
            LogFieeldEnum::ua,        // ユーザーエージェント
            LogFieeldEnum::xff,       // X-Forwarded-For
            LogFieeldEnum::status,    // HTTPステータス
            LogFieeldEnum::method,    // HTTPメソッド
            LogFieeldEnum::uri,       // リクエストURI
            LogFieeldEnum::query,     // クエリパラメータ
            LogFieeldEnum::referer,   // リファラ
        ],
    ],
];
