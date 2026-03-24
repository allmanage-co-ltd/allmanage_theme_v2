<?php

namespace App\Hooks;

use App\Services\Logger\LogFieldResolver;
use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;
use App\Services\Logger\Logger;
use App\Services\Http\Runtime;

/**---------------------------------------------
 * アクセスログ記録クラス
 * ---------------------------------------------
 * - 設定されたフックに対してアクセスログを記録する
 * - Bot・ローカル環境はログ対象外とする
 * - 記録項目・対象フック・チャンネル名は config/logger.php で定義する
 */
class AccessLog implements BootableWpHookInterface
{
    /**
     * フック登録
     *
     * - config で指定されたすべてのフックに log() を登録する
     */
    public function boot(): void
    {
        $config = $this->config();
        foreach ($config['hooks'] as $hook) {
            add_action($hook, $this->log(...));
        }
    }

    /**
     * アクセスログを記録する
     *
     * - Bot・ローカル環境は記録しない
     * - LogFieldResolver でフィールドを解決し Logger::access() に渡す
     */
    public function log(): void
    {
        if (Runtime::isBot() || Runtime::isLocal()) {
            return;
        }

        $config  = $this->config();
        $content = LogFieldResolver::handle($config['content']);

        Logger::access()->info($config['channel'], $content);
    }

    /**
     * アクセスログ設定を取得する
     */
    private function config(): array
    {
        return Config::get('logger.access');
    }
}
