<?php

namespace App\Services;

/**---------------------------------------------
 * WP_Query ビルダークラス
 * ---------------------------------------------
 * - WP_Query の引数生成を責務とする
 * - 配列 args を直接触らせない
 * - メソッドチェーンで直感的に条件を積む
 * - テンプレート側で配列を書かせない
 * - WP_Query の知識をこのクラスに集約する
 * - 複雑な検索条件でも読みやすさを保つ
 */
class MyWpQuery
{
  private array $args = [];
  private int $paged;

  public function __construct()
  {
    // デフォルトargs
    $this->paged = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);
    $this->args  = [
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'paged'          => $this->paged,
    ];
  }

  /**
   * ファクトリメソッド
   */
  public static function new(): self
  {
    return new self();
  }

  /**
   * 現在の args を取得
   *
   * - build 前の確認用
   * - テストやログ用途を想定
   */
  public function args(): array
  {
    return $this->args;
  }

  /**
   * デバッグ用
   *
   * - 現在の args を出力する
   * - $die=true の場合は処理を停止する
   */
  public function debug(bool $die = false): self
  {
    d($this->args);
    if ($die) {
      die;
    }
    return $this;
  }

  /**
   * 組み立てたargsからWP_Query を生成
   */
  public function build(): \WP_Query
  {
    return new \WP_Query($this->args);
  }

  /**
   * 投稿タイプ指定
   */
  public function setPostType(string|array $post_type = ''): self
  {
    $this->args['post_type'] = $post_type;
    return $this;
  }

  /**
   * 1ページあたりの表示件数指定
   */
  public function setPerPage(int $per_page): self
  {
    $this->args['posts_per_page'] = $per_page;
    return $this;
  }

  /**
   * 検索キーワード指定
   */
  public function setSearchQuery(string $search_query): self
  {
    if ($search_query !== '') {
      $this->args['s'] = $search_query;
    }
    return $this;
  }

  /**
   * タクソノミークエリ追加
   */
  public function setTaxQuery(
    string $taxonomy,
    array|string $terms,
    string $field = 'slug',
    string $operator = 'IN'
  ): self {
    $this->args['tax_query'][] = [
      'taxonomy' => $taxonomy,
      'field'    => $field,
      'terms'    => (array) $terms,
      'operator' => $operator,
    ];
    return $this;
  }

  /**
   * メタクエリ追加
   */
  public function setMetaQuery(
    string $key,
    mixed $value,
    string $compare = '='
  ): self {
    $this->args['meta_query'][] = [
      'key'     => $key,
      'value'   => $value,
      'compare' => $compare,
    ];
    return $this;
  }

  /**
   * 並び順指定
   */
  public function setOrderBy(string $orderby, string $order = 'DESC'): self
  {
    $this->args['orderby'] = $orderby;
    $this->args['order']   = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
    return $this;
  }

  /**
   * 日付順ソート
   */
  public function setOrderByDate(string $order = 'DESC'): self
  {
    return $this->setOrderBy('date', $order);
  }

  /**
   * メタ値によるソート
   */
  public function setOrderByMeta(string $meta_key, string $order = 'ASC'): self
  {
    $this->args['meta_key'] = $meta_key;
    $this->args['orderby']  = 'meta_value';
    $this->args['order']    = $order;
    return $this;
  }
}