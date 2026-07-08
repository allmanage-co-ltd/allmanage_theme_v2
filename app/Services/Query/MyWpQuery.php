<?php

namespace App\Services\Query;

/**---------------------------------------------
 * WP_Query ビルダークラス
 * ---------------------------------------------
 * $argsのハードコードが面倒なため、直感的なメソッドチェーンで
 * カスタムクエリを組み貯めるビルダークラス
 */
class MyWpQuery
{
  private array $args = [];
  private readonly int $paged;

  public function __construct()
  {
    // デフォルトargs
    $this->paged = \max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);
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
  public function setPostStatus(string $status): self
  {
    $this->args['post_status'] = $status;
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
   * 除外する投稿を指定
   */
  public function setPostNotIn(array|int $id): self
  {
    if (!empty($id)) {
      $this->args['post__not_in'] = is_array($id) ? $id : [$id];
    }
    return $this;
  }

  /**
   * 指定日付以降
   */
  public function setDateAfter(string $date, bool $inclusive = true): self
  {
    $this->args['date_query'][] = [
      'after'     => $date,
      'inclusive' => $inclusive,
    ];
    return $this;
  }

  /**
   * 指定日付以前
   */
  public function setDateBefore(string $date, bool $inclusive = true): self
  {
    $this->args['date_query'][] = [
      'before'    => $date,
      'inclusive' => $inclusive,
    ];
    return $this;
  }

  /**
   * 指定期間
   */
  public function setDateBetween(string $after, string $before, bool $inclusive = true): self
  {
    $this->args['date_query'][] = [
      'after'     => $after,
      'before'    => $before,
      'inclusive' => $inclusive,
    ];
    return $this;
  }

  /**
   * タクソノミークエリのリレーションを指定
   */
  public function setTaxRelation(string $relation): self
  {
    $this->args['tax_query']['relation'] = $relation;
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
   * タクソノミーアーカイブ用クエリのファクトリ
   *
   * - post_type と tax_query だけ指定して後は固定
   */
  public static function forTaxArchive(
    string|array $post_type,
    int $per_page = 10
  ): self {
    $term     = get_queried_object();
    $taxonomy = $term->taxonomy ?? null;

    return self::new()
      ->setPostType($post_type)
      ->setTaxQuery($taxonomy, $term->term_id, 'term_id')
      ->setPerPage($per_page)
      ->setOrderByDate();
  }

  /**
   * アーカイブ用クエリのファクトリ
   *
   * post_type と件数だけ指定すれば日付降順で取得できるショートハンド
   *
   * 使用例:
   *   MyWpQuery::forArchive('news')->build();
   *   MyWpQuery::forArchive('news', 20)->build();
   *   MyWpQuery::forArchive('news')->setPostNotIn([1, 2, 3])->build();
   */
  public static function forArchive(
    string|array $post_type,
    int $per_page = 10
  ): self {
    return self::new()
      ->setPostType($post_type)
      ->setPerPage($per_page)
      ->setOrderByDate();
  }

  /**
   * メタクエリのリレーションを指定
   */
  public function setMetaRelation(string $relation): self
  {
    $this->args['meta_query']['relation'] = $relation;
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
    $this->args['order']   = \strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
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
    $this->args['order']    = \strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
    return $this;
  }
}
