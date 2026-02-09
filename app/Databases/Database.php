<?php

namespace App\Databases;

use wpdb;

/**---------------------------------------------
 * データベース操作ラッパークラス
 * ---------------------------------------------
 * - WordPress の wpdb を直接触らせないための薄いラッパー
 * - prepare + 実行を毎回強制する
 * - 生SQLを使いつつ、最低限の安全性と統一感を持たせる
 * - $wpdb をグローバルで使い回さない
 * - Service / Admin / UI から直接 wpdb を触らせない
 */
class Database
{
  private wpdb $wpdb;
  private ?string $sql = null;
  private array $params = [];

  public function __construct(wpdb $wpdb)
  {
    $this->wpdb = $wpdb;
  }

  /**
   * ファクトリ関数
   * \App\Databases::new()->stmt('....', ['arg'])->debug();
   * \App\Databases::new()->stmt('....', ['arg'])->get();
   */
  public static function new(): self
  {
    global $wpdb;
    return new self($wpdb);
  }

  /**
   * SQL をセット
   */
  public function stmt(string $sql, array $params = []): self
  {
    $this->sql    = $sql;
    $this->params = $params;
    return $this;
  }

  /**
   * SQL を表示（実行しない）
   */
  public function debug(): self
  {
    $query = $this->wpdb->prepare($this->sql, $this->params);

    echo '<pre style="background:#111;color:#0f0;padding:12px;">';
    echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
    echo '</pre>';

    return $this;
  }

  /**
   * SELECT 単件
   */
  public function get(): ?array
  {
    $query = $this->wpdb->prepare($this->sql, $this->params);
    return $this->wpdb->get_row($query, ARRAY_A);
  }

  /**
   * SELECT 複数件
   */
  public function select(): array
  {
    $query = $this->wpdb->prepare($this->sql, $this->params);
    return $this->wpdb->get_results($query, ARRAY_A) ?? [];
  }

  /**
   * INSERT / UPDATE / DELETE
   */
  public function execute(): int
  {
    $query = $this->wpdb->prepare($this->sql, $this->params);
    return $this->wpdb->query($query);
  }
}