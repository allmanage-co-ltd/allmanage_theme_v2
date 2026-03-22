<?php

namespace App\Hooks;

use App\Actions\Logger\ResolveLogFieldAction;
use App\Interfaces\BootableWpHookInterface;
use App\Support\Config;
use App\Support\Logger;
use App\Support\Runtime;

/**
 * アクセスログ記録
 *
 * 各種設定項目は config/logger.php で定義
 */
class AccessLog implements BootableWpHookInterface
{
    public function boot(): void
    {
        $config = $this->config();
        foreach ($config['hooks'] as $hook) {
            add_action($hook, $this->log(...));
        }
    }

    public function log(): void
    {
        if (Runtime::isBot() || Runtime::isLocal()) {
            return;
        }

        $config  = $this->config();
        $content = ResolveLogFieldAction::handle($config['content']);

        Logger::access()->info($config['channel'], $content);
    }

    private function config(): array
    {
        return Config::get('logger.access');
    }
}
