<?php

namespace App\WordPress\Hooks\Abstracts;

use App\Interfaces\BootableWpHookInterface;
use App\Shared\Logger;

/**
 * アクセスログ記録の共通処理。
 *
 * どのフックで何を記録するかは子クラスに任せ、記録の流れだけを揃える。
 */
abstract class AccessLogAbstract implements BootableWpHookInterface
{
    abstract protected function hooks(): array;

    abstract protected function disableRuntimes(): bool;

    abstract protected function loggerName(): string;

    abstract protected function content(): array;

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
