<?php

namespace App\CMS\Presenter;

/**---------------------------------------------
 *
 * ---------------------------------------------
 */
class PostNavigation
{
    private $prev;
    private $next;

    public function __construct(
        private readonly string $archive_url,
        private readonly string $archive_text = '一覧へ戻る',
        private readonly string $prev_text = '← 前へ',
        private readonly string $next_text = '次へ →',
    ) {
        $this->prev = get_previous_post();
        $this->next = get_next_post();
    }

    public function render(): void
    {
        $prev_html = '';
        $next_html = '';

        if ($this->prev) {
            $url = get_permalink($this->prev);

            $prev_html = <<<HTML
            <a href="{$url}" class="wp-postnav__prev">
                <span class="wp-postnav__txt">{$this->prev_text}</span>
            </a>
            HTML;
        }

        if ($this->next) {
            $url = get_permalink($this->next);

            $next_html = <<<HTML
            <a href="{$url}" class="wp-postnav__next">
                <span class="wp-postnav__txt">{$this->next_text}</span>
            </a>
            HTML;
        }

        echo <<<HTML
        <div class="wp-postnav">
            {$prev_html}

            <a href="{$this->archive_url}" class="wp-postnav__archive">
                {$this->archive_text}
            </a>

            {$next_html}
        </div>
        HTML;
    }
}
