<?php

/**
 * 閲覧ログ
 *
 * どのセクションがいつ・誰に見られたかを専用テーブルに記録し、管理者向けに集計する。
 * テーブルは初回アクセス時に自動作成（dbDelta）。
 */

namespace WpManual;

if (!defined('ABSPATH')) {
    exit;
}

class Log
{
    const DB_VERSION = '1';

    /** @var \wpdb */
    private $wpdb;

    /** @var string */
    private $table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb  = $wpdb;
        $this->table = $wpdb->prefix . 'wp_manual_views';
    }

    public function table(): string
    {
        return $this->table;
    }

    /**
     * テーブルが無ければ作成（オプションでバージョン管理し、毎回 SHOW TABLES しない）
     */
    public function ensure_table(): void
    {
        if (get_option('wp_manual_db_version') === self::DB_VERSION) {
            return;
        }
        $charset = $this->wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            section VARCHAR(100) NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            viewed_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY section (section),
            KEY viewed_at (viewed_at)
        ) {$charset};";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        update_option('wp_manual_db_version', self::DB_VERSION, false);
    }

    public function drop(): void
    {
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->table}");
        delete_option('wp_manual_db_version');
    }

    public function record(string $section, int $user_id, bool $is_admin): void
    {
        $this->ensure_table();
        $this->wpdb->insert($this->table, [
            'section'   => $section,
            'user_id'   => $user_id,
            'is_admin'  => $is_admin ? 1 : 0,
            'viewed_at' => current_time('mysql'),
        ], ['%s', '%d', '%d', '%s']);
    }

    /**
     * セクション別の集計
     * 戻り値：section => ['total','recent','recent_editor','last','last_user']
     */
    public function summary(int $days = 30): array
    {
        $this->ensure_table();
        $since = gmdate('Y-m-d H:i:s', current_time('timestamp') - $days * DAY_IN_SECONDS);

        $rows = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT section,
                    COUNT(*) AS total,
                    SUM(CASE WHEN viewed_at >= %s THEN 1 ELSE 0 END) AS recent,
                    SUM(CASE WHEN viewed_at >= %s AND is_admin = 0 THEN 1 ELSE 0 END) AS recent_editor,
                    MAX(viewed_at) AS last
             FROM {$this->table}
             GROUP BY section",
            $since,
            $since
        ), ARRAY_A);

        $result = [];
        foreach ($rows ?: [] as $r) {
            $result[$r['section']] = [
                'total'         => (int) $r['total'],
                'recent'        => (int) $r['recent'],
                'recent_editor' => (int) $r['recent_editor'],
                'last'          => $r['last'],
            ];
        }
        return $result;
    }

    /**
     * 直近の閲覧（誰が・いつ・どこを）
     */
    public function recent(int $limit = 20): array
    {
        $this->ensure_table();
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT section, user_id, is_admin, viewed_at FROM {$this->table} ORDER BY viewed_at DESC LIMIT %d",
            $limit
        ), ARRAY_A) ?: [];
    }

    /**
     * 古いログの削除（既定 365 日）
     */
    public function prune(int $days = 365): void
    {
        $this->ensure_table();
        $before = gmdate('Y-m-d H:i:s', current_time('timestamp') - $days * DAY_IN_SECONDS);
        $this->wpdb->query($this->wpdb->prepare("DELETE FROM {$this->table} WHERE viewed_at < %s", $before));
    }
}
