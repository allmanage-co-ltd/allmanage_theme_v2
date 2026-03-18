<?php

namespace App\CMS\Hooks;

use App\Support\Logger;

/**---------------------------------------------
 * アクセスログ 基底クラス
 * ---------------------------------------------
 * - アクセスログの記録処理を提供する抽象クラス
 * - ログの内容・条件・フックはサブクラスに委ねる
 * - フック登録とログ記録の責務はこのクラスに集約する
 */
abstract class AccessLogAbstract extends Hook
{
    /**
     * ログを記録するWordPressフックの一覧
     *
     * - 例: ['template_redirect', 'admin_init']
     */
    abstract protected function hooks(): array;

    /**
     * ログを無効にするランタイム条件
     *
     * - 戻り値が true の場合はログを記録しない
     */
    abstract protected function disableRuntimes(): bool;

    /**
     * ログのチャンネル名
     */
    abstract protected function loggerName(): string;

    /**
     * ログに記録するコンテンツ
     *
     * - 連想配列で返す
     */
    abstract protected function content(): array;

    /**
     * フック登録
     *
     * - hooks() で定義したフック全てに log() を登録する
     */
    #[\Override]
    public function boot(): void
    {
        foreach ($this->hooks() as $hook) {
            add_action($hook, $this->log(...));
        }
    }

    /**
     * アクセスログを記録
     *
     * - disableRuntimes() が true の場合は記録しない
     * - Logger::access() にチャンネル名とコンテンツを渡して記録する
     */
    public function log(): void
    {
        if ($this->disableRuntimes()) {
            return;
        }

        Logger::access()->info($this->loggerName(), $this->content());
    }
}
