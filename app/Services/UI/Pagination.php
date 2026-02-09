<?php

namespace App\Services\UI;

use WP_Query;

/**---------------------------------------------
 * ページネーション生成クラス
 * ---------------------------------------------
 * - WP_Query を元にページャー用HTMLを生成する
 * - テンプレートからページ計算ロジックを分離する
 * - 現在ページ・総ページ数・表示範囲を内部で管理する
 */
class Pagination
{
  // 現在のページ番号
  private int $paged;

  // 総ページ数
  private int $pages;

  // 表示するページ番号の最大数
  private int $range;

  // 対象WP_Query
  private WP_Query $query;

  /**
   * コンストラクタ
   *
   * - WP_Query からページ情報を取得する
   * - paged / max_num_pages を内部状態として保持
   */
  public function __construct(
    WP_Query $query,
    int $range = 5
  ) {
    $this->query = $query;
    $this->paged = max(1, (int) ($query->get('paged') ?: 1));
    $this->pages = (int) ($query->max_num_pages ?: 1);
    $this->range = $range;
  }

  /**
   * ページャーHTML生成
   *
   * - ページ数が1以下の場合は何も出力しない
   * - 前へ / 次へ / ページ番号リンクを組み立てる
   */
  public function render(): string
  {
    if ($this->pages <= 1) {
      return '';
    }

    [$start, $end] = $this->calculateRange();

  return <<<HTML
<div class="wp-pager">
  <ul class="wp-pager__list">
    <li class="wp-pager__item -first">{$this->prev()}</li>
    {$this->pageLinks($start, $end)}
    <li class="wp-pager__item -last">{$this->next()}</li>
  </ul>
</div>
HTML;
  }

  /**
   * ページ番号リンク生成
   *
   * - 指定された開始〜終了番号のリンクを生成する
   * - 現在ページには current クラスを付与する
   */
  private function pageLinks(int $start, int $end): string
  {
    $html = '';

    for ($i = $start; $i <= $end; $i++) {
      $active = $this->paged === $i ? '-current current' : '';
      $link   = $this->link($i);

      $html .= <<<HTML
<li class="wp-pager__item {$active}">
  <a href="{$link}" class="page-numbers">{$i}</a>
</li>
HTML;
    }

    return $html;
  }

  /**
   * 前ページリンク生成
   *
   * - 1ページ目の場合は表示しない
   */
  private function prev(): string
  {
    if ($this->paged <= 1) {
      return '';
    }

    return <<<HTML
<a href="{$this->link($this->paged - 1)}" class="prev page-numbers">
  ←
</a>
HTML;
  }

  /**
   * 次ページリンク生成
   *
   * - 最終ページの場合は表示しない
   */
  private function next(): string
  {
    if ($this->paged >= $this->pages) {
      return '';
    }

    return <<<HTML
<a href="{$this->link($this->paged + 1)}" class="next page-numbers">
  →
</a>
HTML;
  }

  /**
   * ページURL生成
   *
   * - get_pagenum_link を利用してURLを生成する
   * - 固定ページ / カスタム投稿 / アーカイブに対応
   */
  private function link(int $page): string
  {
    $big = 999999999;

    return str_replace(
      (string) $big,
      (string) $page,
      get_pagenum_link($big)
    );
  }

  /**
   * 表示ページ範囲計算
   *
   * - 現在ページを中心に表示範囲を決定する
   * - 先頭寄り / 末尾寄り / 中央寄りのケースを考慮
   * - [start, end] の配列を返す
   */
  private function calculateRange(): array
  {
    $center = (int) ceil($this->range / 2);
    $minus  = $center - 1;
    $plus   = $this->range % 2 === 0 ? $minus + 1 : $minus;
    $col    = ($this->pages - $this->range) + 1;

    $start = 1;
    $end   = $this->pages;

    if ($this->pages > $this->range) {
      if ($this->paged <= $center) {
        $start = 1;
        $end   = $this->range;
      } elseif ($this->paged + $minus >= $this->pages) {
        $start = $col;
        $end   = $this->pages;
      } else {
        $start = $this->paged - $minus;
        $end   = $this->paged + $plus;
      }
    }

    return [$start, $end];
  }
}