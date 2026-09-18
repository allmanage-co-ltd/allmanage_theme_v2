<?php

/**
 * 管理画面「マニュアル」ページ
 *
 * - 閲覧・補足メモ・FAQ編集：WP_MANUAL_VIEW_CAP（編集者以上）
 * - 表示/非表示・閲覧ログ：WP_MANUAL_ADMIN_CAP（管理者）
 * - 管理バー / メタボックスから「今いる画面のマニュアル」へ誘導
 */

namespace WpManual;

if (!defined('ABSPATH')) {
    exit;
}

class Admin
{
    /** @var self|null */
    private static $instance = null;

    /** 現在画面 → セクション判定に使う対象一覧のキャッシュ */
    const TARGETS_TRANSIENT = 'wp_manual_targets';

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_bar_menu', [$this, 'admin_bar'], 90);
        add_action('add_meta_boxes', [$this, 'add_meta_box'], 10, 2);

        add_action('admin_post_wp_manual_save_note', [$this, 'save_note']);
        add_action('admin_post_wp_manual_save_faq', [$this, 'save_faq']);
        add_action('admin_post_wp_manual_toggle', [$this, 'toggle_section']);
        add_action('wp_ajax_wp_manual_log', [$this, 'ajax_log']);

        // 構成が変わったら対象一覧キャッシュを捨てる
        add_action('acf/save_post', [$this, 'flush_targets']);
        add_action('save_post_acf-field-group', [$this, 'flush_targets']);
        add_action('registered_post_type', [$this, 'flush_targets']);
    }

    public function add_menu(): void
    {
        add_menu_page(
            '管理画面マニュアル',
            'マニュアル',
            WP_MANUAL_VIEW_CAP,
            WP_MANUAL_MENU_SLUG,
            [$this, 'render'],
            'dashicons-book-alt',
            3
        );
    }

    public function enqueue(string $hook): void
    {
        if ($hook !== 'toplevel_page_' . WP_MANUAL_MENU_SLUG) {
            return;
        }
        wp_enqueue_style('dashicons');
        wp_enqueue_style('wp-manual', WP_MANUAL_URL . '/assets/manual.css', ['dashicons'], $this->asset_version('assets/manual.css'));
        wp_enqueue_script('wp-manual', WP_MANUAL_URL . '/assets/manual.js', [], $this->asset_version('assets/manual.js'), true);
        wp_localize_script('wp-manual', 'wpManual', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wp_manual_log'),
        ]);
    }

    private function asset_version(string $relative): string
    {
        $path = WP_MANUAL_DIR . '/' . $relative;
        return file_exists($path) ? (string) filemtime($path) : '1.0.0';
    }

    // ------------------------------------------------------------------
    // 「今いる画面のマニュアル」導線
    // ------------------------------------------------------------------

    public function flush_targets(): void
    {
        delete_transient(self::TARGETS_TRANSIENT);
    }

    private function targets(): array
    {
        $t = get_transient(self::TARGETS_TRANSIENT);
        if (!is_array($t)) {
            $t = (new Inspector())->targets();
            set_transient(self::TARGETS_TRANSIENT, $t, 10 * MINUTE_IN_SECONDS);
        }
        return $t;
    }

    /**
     * 現在の管理画面に対応するセクションID（該当なしは空文字）
     */
    private function section_for_current_screen(): string
    {
        if (!function_exists('get_current_screen')) {
            return '';
        }
        $screen = get_current_screen();
        if (!$screen) {
            return '';
        }
        $targets = $this->targets();

        // 投稿編集画面：特定ページ専用セクションがあれば優先
        if ($screen->base === 'post' && !empty($_GET['post'])) {
            $post_id = (int) $_GET['post'];
            if (in_array($post_id, $targets['pages'], true)) {
                return 'page-' . $post_id;
            }
        }
        if ($screen->post_type) {
            if (in_array($screen->post_type, $targets['inquiries'], true)) {
                return 'inq-' . $screen->post_type;
            }
            if (in_array($screen->post_type, $targets['post_types'], true)) {
                return 'pt-' . $screen->post_type;
            }
        }
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if ($page !== '' && in_array($page, $targets['options_pages'], true)) {
            return 'op-' . $page;
        }
        if ($screen->base === 'nav-menus') {
            return 'menus';
        }
        if (in_array($screen->base, ['upload', 'media'], true)) {
            return 'media';
        }
        return '';
    }

    private function manual_url(string $section = ''): string
    {
        $url = admin_url('admin.php?page=' . WP_MANUAL_MENU_SLUG);
        return $section !== '' ? $url . '#' . $section : $url;
    }

    public function admin_bar(\WP_Admin_Bar $bar): void
    {
        if (!current_user_can(WP_MANUAL_VIEW_CAP)) {
            return;
        }
        $section = $this->section_for_current_screen();
        $bar->add_node([
            'id'    => 'wp-manual',
            'title' => '<span class="ab-icon dashicons dashicons-book-alt" style="top:2px;"></span> ' . ($section !== '' ? 'この画面のマニュアル' : 'マニュアル'),
            'href'  => $this->manual_url($section),
            'meta'  => ['target' => '_blank'],
        ]);
        if ($section !== '') {
            $bar->add_node([
                'parent' => 'wp-manual',
                'id'     => 'wp-manual-top',
                'title'  => 'マニュアルの先頭へ',
                'href'   => $this->manual_url(),
                'meta'   => ['target' => '_blank'],
            ]);
        }
    }

    /**
     * 投稿編集画面の右サイドに「この画面の使い方」を表示
     */
    public function add_meta_box(string $post_type, $post): void
    {
        if (!current_user_can(WP_MANUAL_VIEW_CAP)) {
            return;
        }
        $targets = $this->targets();
        $post_id = $post instanceof \WP_Post ? $post->ID : 0;

        if (in_array($post_id, $targets['pages'], true)) {
            $section = 'page-' . $post_id;
        } elseif (in_array($post_type, $targets['post_types'], true)) {
            $section = 'pt-' . $post_type;
        } else {
            return;
        }

        add_meta_box(
            'wp-manual-help',
            'この画面の使い方',
            function () use ($section) {
                printf(
                    '<p style="margin:4px 0 10px;">入力項目の説明と更新手順をマニュアルで確認できます。</p>
                     <a href="%s" target="_blank" rel="noopener" class="button" style="display:inline-flex;align-items:center;gap:4px;"><span class="dashicons dashicons-book-alt" style="line-height:1;"></span> マニュアルを開く</a>',
                    esc_url($this->manual_url($section))
                );
            },
            null,
            'side',
            'high'
        );
    }

    // ------------------------------------------------------------------
    // 保存処理
    // ------------------------------------------------------------------

    private function back(string $section, string $msg): void
    {
        wp_safe_redirect($this->manual_url() . '&wpm_msg=' . rawurlencode($msg) . '#' . $section);
        exit;
    }

    private function get_array_option(string $key): array
    {
        $v = get_option($key, []);
        return is_array($v) ? $v : [];
    }

    /**
     * 表示設定を取得（旧形式 wp_manual_hidden = 非表示IDの配列 があれば移行する）
     */
    private function get_visibility(): array
    {
        $visibility = $this->get_array_option(WP_MANUAL_OPT_VISIBILITY);
        $legacy     = get_option('wp_manual_hidden', null);
        if (is_array($legacy)) {
            foreach ($legacy as $id) {
                $visibility[$id] = 'hidden';
            }
            update_option(WP_MANUAL_OPT_VISIBILITY, $visibility, false);
            delete_option('wp_manual_hidden');
        }
        return $visibility;
    }

    public function save_note(): void
    {
        if (!current_user_can(WP_MANUAL_VIEW_CAP)) {
            wp_die('権限がありません。');
        }
        check_admin_referer('wp_manual_note');

        $section = sanitize_key($_POST['section'] ?? '');
        $note    = sanitize_textarea_field(wp_unslash($_POST['note'] ?? ''));

        $notes = $this->get_array_option(WP_MANUAL_OPT_NOTES);
        if ($note === '') {
            unset($notes[$section]);
        } else {
            $notes[$section] = $note;
        }
        update_option(WP_MANUAL_OPT_NOTES, $notes, false);
        $this->back($section, '補足メモを保存しました。');
    }

    public function save_faq(): void
    {
        if (!current_user_can(WP_MANUAL_VIEW_CAP)) {
            wp_die('権限がありません。');
        }
        check_admin_referer('wp_manual_faq');

        $faq = $this->get_array_option(WP_MANUAL_OPT_FAQ);
        $id  = sanitize_key($_POST['faq_id'] ?? '');

        if (!empty($_POST['delete'])) {
            unset($faq[$id]);
            update_option(WP_MANUAL_OPT_FAQ, $faq, false);
            $this->back('faq', '質問を削除しました。');
        }

        $q = sanitize_text_field(wp_unslash($_POST['q'] ?? ''));
        $a = sanitize_textarea_field(wp_unslash($_POST['a'] ?? ''));
        if ($q === '' || $a === '') {
            $this->back('faq', '質問と回答の両方を入力してください。');
        }
        if ($id === '') {
            $id = 'faq_' . wp_generate_password(6, false);
        }
        $faq[$id] = ['q' => $q, 'a' => $a];
        update_option(WP_MANUAL_OPT_FAQ, $faq, false);
        $this->back('faq', 'FAQを保存しました。');
    }

    public function toggle_section(): void
    {
        if (!current_user_can(WP_MANUAL_ADMIN_CAP)) {
            wp_die('権限がありません。');
        }
        check_admin_referer('wp_manual_toggle');

        $section    = sanitize_key($_POST['section'] ?? '');
        $visibility = $this->get_visibility();

        // 現在の実効状態（デフォルト含む）を反転して明示保存する
        $renderer   = new Renderer([], [], $visibility, [], [], false, true);
        $now_hidden = $renderer->is_hidden($section);

        $visibility[$section] = $now_hidden ? 'visible' : 'hidden';
        $msg = $now_hidden
            ? '項目を表示にしました（編集者にも表示されます）。'
            : '項目を非表示にしました（管理者以外には表示されません）。';

        update_option(WP_MANUAL_OPT_VISIBILITY, $visibility, false);
        $this->back($section, $msg);
    }

    /**
     * 閲覧ログ記録（タブを開いた時に JS から sendBeacon で送られる）
     */
    public function ajax_log(): void
    {
        if (!current_user_can(WP_MANUAL_VIEW_CAP) || !check_ajax_referer('wp_manual_log', 'nonce', false)) {
            wp_send_json_error(null, 403);
        }
        $section = sanitize_key($_POST['section'] ?? '');
        if ($section === '' || $section === 'log') {
            wp_send_json_success();
        }
        (new Log())->record($section, get_current_user_id(), current_user_can(WP_MANUAL_ADMIN_CAP));
        wp_send_json_success();
    }

    // ------------------------------------------------------------------
    // 描画
    // ------------------------------------------------------------------

    public function render(): void
    {
        if (!current_user_can(WP_MANUAL_VIEW_CAP)) {
            return;
        }

        $is_admin = current_user_can(WP_MANUAL_ADMIN_CAP);
        $can_note = current_user_can(WP_MANUAL_VIEW_CAP);

        if (get_option(WP_MANUAL_OPT_FAQ, null) === null) {
            update_option(WP_MANUAL_OPT_FAQ, Renderer::default_faq(), false);
        }

        $info    = (new Inspector())->inspect();
        $notes   = $this->get_array_option(WP_MANUAL_OPT_NOTES);
        $visibility = $this->get_visibility();
        $faq     = $this->get_array_option(WP_MANUAL_OPT_FAQ);

        // 閲覧ログは管理者だけ集計を渡す
        $log = [];
        if ($is_admin) {
            $logger = new Log();
            if (mt_rand(1, 50) === 1) {
                $logger->prune();
            }
            $log = [
                'summary' => $logger->summary(30),
                'recent'  => $logger->recent(20),
            ];
        }

        $sections = (new Renderer($info, $notes, $visibility, $faq, $log, $can_note, $is_admin))->sections();

        if (!$is_admin) {
            $sections = array_values(array_filter($sections, function ($s) {
                return empty($s['hidden']);
            }));
        }

        $message = isset($_GET['wpm_msg']) ? sanitize_text_field(rawurldecode($_GET['wpm_msg'])) : '';

        include WP_MANUAL_DIR . '/templates/manual-page.php';
    }
}
