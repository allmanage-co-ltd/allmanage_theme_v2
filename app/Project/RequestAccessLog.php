<?php

namespace App\Project;

use App\WordPress\Hooks\Abstracts\AccessLogAbstract;
use App\Shared\Runtime;

/**---------------------------------------------
 * アクセスログ
 * ---------------------------------------------
 * - フロント・管理画面両方のアクセスを記録する
 * - 案件や環境に応じて各メソッドを自由に変更してください
 */
final class RequestAccessLog extends AccessLogAbstract
{
    /**
     * ログを無効にするランタイム条件
     *
     * - 戻り値が true の場合はログを記録しない
     * - ローカルでログが必要な場合は Runtime::isLocal() を削除
     */
    protected function disableRuntimes(): bool
    {
        return Runtime::isBot() || Runtime::isLocal();
    }

    /**
     * ログを記録するフック
     *
     * - template_redirect: フロントのリクエスト
     * - admin_init: 管理画面のリクエスト
     */
    protected function hooks(): array
    {
        return [
            'template_redirect',
            'admin_init',
        ];
    }

    /**
     * ログのチャンネル名
     */
    protected function loggerName(): string
    {
        return 'request';
    }

    /**
     * ログに記録するコンテンツ
     */
    protected function content(): array
    {
        return [
            'ip'      => $_SERVER['REMOTE_ADDR'] ?: '',            // クライアントIP
            'xff'     => $_SERVER['HTTP_X_FORWARDED_FOR'] ?: '',   // プロキシ経由のIP
            'method'  => $_SERVER['REQUEST_METHOD'] ?: '',         // HTTPメソッド
            'uri'     => $_SERVER['REQUEST_URI'] ?: '',            // リクエストURI
            'query'   => $_SERVER['QUERY_STRING'] ?: '',           // クエリ文字列
            'referer' => $_SERVER['HTTP_REFERER'] ?: '',           // リファラー
            'ua'      => $_SERVER['HTTP_USER_AGENT'] ?: '',        // ユーザーエージェント
            'user_id' => get_current_user_id(),                    // ログイン中のユーザーID（未ログインは0）
            'post_id' => get_the_ID(),                             // 投稿ID
            'type'    => get_post_type(),                          // 投稿タイプ
            'status'  => http_response_code(),                     // HTTPステータスコード
            'is_404'  => is_404(),                                 // 404ページかどうか
        ];
    }
}
