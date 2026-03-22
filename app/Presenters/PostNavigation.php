<?php

namespace App\Presenter;

/**---------------------------------------------
 * 投稿ナビゲーション生成クラス
 * ---------------------------------------------
 * - 単一投稿ページの前後ナビゲーションを生成する
 * - WordPressの前後記事取得ロジックをテンプレートから分離する
 * - 前の記事 / 一覧 / 次の記事リンクHTMLを生成する
 */
class PostNavigation
{
    // 前の記事
    private readonly mixed $prev;

    // 次の記事
    private readonly mixed $next;

    /**
     * コンストラクタ
     *
     * - 前後の記事を取得する
     * - ナビゲーション表示用のテキストを設定する
     */
    public function __construct(
        private readonly string $archive_url,
        private readonly string $archive_text = '一覧へ戻る',
        private readonly string $prev_text = '← 前へ',
        private readonly string $next_text = '次へ →',
    ) {
        $this->prev = get_previous_post();
        $this->next = get_next_post();
    }

    /**
     * ナビゲーションHTML出力
     *
     * - 前後記事リンクを生成
     * - 一覧ページリンクを生成
     * - 前後記事が存在しない場合は該当リンクを出力しない
     */
    public function render(): void
    {
        $prev_html = '';
        $next_html = '';

        if ($this->prev) {
            $url = get_permalink($this->prev);

            $prev_html = <<<HTML
            <a href="{$url}" class="wp-postnavi__prev">
                <span class="wp-postnavi__txt">{$this->prev_text}</span>
            </a>
            HTML;
        }

        if ($this->next) {
            $url = get_permalink($this->next);

            $next_html = <<<HTML
            <a href="{$url}" class="wp-postnavi__next">
                <span class="wp-postnavi__txt">{$this->next_text}</span>
            </a>
            HTML;
        }

        echo <<<HTML
        <div class="wp-postnavi">
            {$prev_html}

            <a href="{$this->archive_url}" class="wp-postnavi__archive">
                {$this->archive_text}
            </a>

            {$next_html}
        </div>
        HTML;
    }
}
