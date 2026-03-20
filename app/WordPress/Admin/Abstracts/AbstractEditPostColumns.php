<?php

namespace App\WordPress\Admin\Abstracts;

use App\WordPress\Admin\Admin;
use App\WordPress\Plugins\Acf;

/**
 * 投稿一覧カラム追加の共通処理。
 *
 * カラム登録とフック接続はここで揃え、案件ごとの列定義だけを子クラスへ残す。
 */
abstract class AbstractEditPostColumns extends Admin
{
    abstract protected function postType(): string;

    abstract protected function useAcf(): bool;

    abstract protected function columns(): array;

    #[\Override]
    public function boot(): void
    {
        if ($this->useAcf() && !Acf::isActive()) {
            return;
        }

        add_filter("manage_{$this->postType()}_posts_columns", $this->register(...));
        add_action("manage_{$this->postType()}_posts_custom_column", $this->edit(...), 10, 2);
    }

    public function register($columns)
    {
        foreach ($this->columns() as $key => $label) {
            $columns[$key] = $label;
        }

        return $columns;
    }

    abstract public function edit($column, $postId): void;
}
