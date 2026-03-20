<?php

namespace App\WordPress\Hooks\Abstracts;

use App\Support\Logger;
use App\WordPress\Hooks\Hook;

/**
 * アクセスログ記録の共通処理。
 *
 * どのフックで何を記録するかは子クラスに任せ、記録の流れだけを揃える。
 */
abstract class AbstractAccessLog extends Hook
{
    abstract protected function hooks(): array;

    abstract protected function disableRuntimes(): bool;

    abstract protected function loggerName(): string;

    abstract protected function content(): array;

    #[\Override]
    public function boot(): void
    {
        foreach ($this->hooks() as $hook) {
            add_action($hook, $this->log(...));
        }
    }

    public function log(): void
    {
        if ($this->disableRuntimes()) {
            return;
        }

        Logger::access()->info($this->loggerName(), $this->content());
    }
}
