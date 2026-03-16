<?php

namespace App\CMS\Hooks;

use App\Support\Logger;
use App\Support\Runtime;

/**---------------------------------------------
 * アクセスログフッククラス
 * ---------------------------------------------
 * - template_redirect時にアクセスログを記録する
 */
class AccessLog extends Hook
{
    /**
     * フック登録
     */
    #[\Override]
    public function boot(): void
    {
        add_action('template_redirect', $this->log(...));
    }

    /**
     * アクセスログを記録
     */
    public function log(): void
    {
        if (Runtime::isBot() || Runtime::isLocal()) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/wp-content/')) {
            return;
        }

        Logger::access()->info('request', [
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
            'xff'     => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
            'method'  => $_SERVER['REQUEST_METHOD'] ?? '',
            'uri'     => $_SERVER['REQUEST_URI'] ?? '',
            'query'   => $_SERVER['QUERY_STRING'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_id' => get_current_user_id(),
            'post_id' => get_the_ID(),
            'type'    => get_post_type(),
            'status'  => http_response_code(),
            'is_404'  => is_404(),
        ]);
    }
}
