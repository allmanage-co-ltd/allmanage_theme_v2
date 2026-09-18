<?php

/**
 * サイト構成の解析
 *
 * 投稿タイプ / タクソノミー / ACF フィールド（location 完全対応・制約・表示条件） /
 * オプションページ / 固定ページ個別 / プラグイン / メニュー を配列に正規化して返す。
 * 描画には関与しない。
 */

namespace WpManual;

if (!defined('ABSPATH')) {
    exit;
}

class Inspector
{
    /** マニュアルに載せない投稿タイプ（フィルタ wp_manual/exclude_post_types で追加可） */
    const DEFAULT_EXCLUDE = [
        'post',          // 標準の「投稿」は使わない運用のため除外（必要なら wp_manual/exclude_post_types で外す）
        'attachment',
        'revision',
        'nav_menu_item',
        'custom_css',
        'customize_changeset',
        'oembed_cache',
        'user_request',
        'wp_block',
        'wp_template',
        'wp_template_part',
        'wp_global_styles',
        'wp_navigation',
        'wp_font_family',
        'wp_font_face',
        'acf-field-group',
        'acf-field',
        'acf-post-type',
        'acf-taxonomy',
        'acf-ui-options-page',
        'wpcf7_contact_form',
        'mw-wp-form',
    ];

    /** @var array ACF の location を解析した結果（post_types / pages / options_pages） */
    private $acf = ['post_types' => [], 'pages' => [], 'options_pages' => []];

    /** @var array フィールドキー => ['label','type','choices']（表示条件の文章化に使う） */
    private $field_index = [];

    /** @var array テンプレートファイル => 表示名 */
    private $templates = [];

    public function inspect(): array
    {
        $this->templates = wp_get_theme()->get_page_templates();
        $this->collect_acf();

        return [
            'site'          => $this->site(),
            'post_types'    => $this->post_types(),
            'pages'         => $this->pages(),
            'options_pages' => $this->options_pages(),
            'inquiries'     => $this->inquiries(),
            'menus'         => $this->menus(),
            'plugins'       => $this->plugins(),
            'has_acf'       => function_exists('acf_get_field_groups'),
        ];
    }

    /**
     * 現在の管理画面に対応するセクションIDの判定に使う「対象一覧」だけを軽量に返す
     */
    public function targets(): array
    {
        $this->templates = wp_get_theme()->get_page_templates();
        $this->collect_acf();
        return [
            'post_types'    => array_keys($this->post_types()),
            'pages'         => array_keys($this->acf['pages']),
            'options_pages' => array_keys($this->acf['options_pages']),
            'inquiries'     => array_map(function ($i) { return $i['slug']; }, $this->inquiries()),
        ];
    }

    private function site(): array
    {
        return [
            'name'       => get_bloginfo('name'),
            'url'        => home_url('/'),
            'admin_url'  => admin_url(),
            'wp_version' => get_bloginfo('version'),
        ];
    }

    // ------------------------------------------------------------------
    // 投稿タイプ
    // ------------------------------------------------------------------

    private function excluded(): array
    {
        return apply_filters('wp_manual/exclude_post_types', self::DEFAULT_EXCLUDE);
    }

    private function post_types(): array
    {
        $excluded = $this->excluded();
        $result   = [];

        foreach (get_post_types(['show_ui' => true], 'objects') as $slug => $obj) {
            if (in_array($slug, $excluded, true) || $this->is_inquiry_type($slug)) {
                continue;
            }
            $result[$slug] = [
                'slug'         => $slug,
                'label'        => $obj->labels->name,
                'singular'     => $obj->labels->singular_name,
                'description'  => $obj->description,
                'builtin'      => (bool) $obj->_builtin,
                'hierarchical' => (bool) $obj->hierarchical,
                'editor'       => $this->editor_type($slug),
                'supports'     => array_keys(get_all_post_type_supports($slug)),
                'taxonomies'   => $this->taxonomies($slug),
                'field_groups' => $this->acf['post_types'][$slug] ?? [],
                'count'        => (int) wp_count_posts($slug)->publish,
                'urls'         => [
                    'list' => admin_url('edit.php?post_type=' . $slug),
                    'new'  => admin_url('post-new.php?post_type=' . $slug),
                ],
            ];
        }

        uasort($result, function ($a, $b) {
            if ($a['builtin'] !== $b['builtin']) {
                return $a['builtin'] ? -1 : 1;
            }
            return strcmp($a['label'], $b['label']);
        });
        return $result;
    }

    /**
     * ACF が「特定の固定ページ / 記事」に紐付いている場合、その記事を個別セクションにする
     */
    private function pages(): array
    {
        $result = [];
        foreach ($this->acf['pages'] as $post_id => $groups) {
            $post = get_post($post_id);
            if (!$post) {
                continue;
            }
            $pto = get_post_type_object($post->post_type);
            $result[] = [
                'id'           => $post_id,
                'title'        => get_the_title($post),
                'post_type'    => $post->post_type,
                'type_label'   => $pto ? $pto->labels->singular_name : $post->post_type,
                'editor'       => $this->editor_type($post->post_type),
                'field_groups' => $groups,
                'url'          => get_edit_post_link($post_id, 'raw'),
                'view_url'     => get_permalink($post_id),
            ];
        }
        return $result;
    }

    private function is_inquiry_type(string $slug): bool
    {
        return strpos($slug, 'mwf_') === 0;
    }

    private function inquiries(): array
    {
        $result = [];
        foreach (get_post_types(['show_ui' => true], 'objects') as $slug => $obj) {
            if (!$this->is_inquiry_type($slug)) {
                continue;
            }
            $counts = wp_count_posts($slug);
            $result[] = [
                'slug'  => $slug,
                'label' => $obj->labels->name,
                'count' => (int) ($counts->publish ?? 0) + (int) ($counts->private ?? 0),
                'url'   => admin_url('edit.php?post_type=' . $slug),
            ];
        }
        return $result;
    }

    private function editor_type(string $post_type): string
    {
        if (!post_type_supports($post_type, 'editor')) {
            return 'none';
        }
        if (function_exists('use_block_editor_for_post_type') && use_block_editor_for_post_type($post_type)) {
            return 'block';
        }
        return 'classic';
    }

    private function taxonomies(string $post_type): array
    {
        $result = [];
        foreach (get_object_taxonomies($post_type, 'objects') as $tax) {
            if (!$tax->show_ui) {
                continue;
            }
            $result[] = [
                'slug'         => $tax->name,
                'label'        => $tax->labels->name,
                'hierarchical' => (bool) $tax->hierarchical,
                'url'          => admin_url('edit-tags.php?taxonomy=' . $tax->name . '&post_type=' . $post_type),
            ];
        }
        return $result;
    }

    // ------------------------------------------------------------------
    // ACF：location ルールの解析
    // ------------------------------------------------------------------

    /**
     * 全フィールドグループを location ルールごとに振り分ける
     * 同じグループが複数の条件で同じ対象に付く場合は条件を「または」で結合する
     */
    private function collect_acf(): void
    {
        if (!function_exists('acf_get_field_groups')) {
            return;
        }
        $excluded = $this->excluded();

        foreach (acf_get_field_groups() as $group) {
            if (isset($group['active']) && !$group['active']) {
                continue;
            }
            $raw_fields = acf_get_fields($group['key']) ?: [];
            $this->index_fields($raw_fields);
            $normalized = [
                'key'         => $group['key'],
                'title'       => $group['title'],
                'description' => $group['description'] ?? '',
                'fields'      => $this->fields($raw_fields),
            ];

            foreach ((array) ($group['location'] ?? []) as $and_rules) {
                $targets = $this->resolve_location($and_rules, $excluded);
                foreach ($targets as $t) {
                    $this->attach($t['bucket'], $t['key'], $normalized, $t['condition']);
                }
            }
        }
    }

    private function attach(string $bucket, $key, array $group, string $condition): void
    {
        if (!isset($this->acf[$bucket][$key])) {
            $this->acf[$bucket][$key] = [];
        }
        foreach ($this->acf[$bucket][$key] as &$existing) {
            if ($existing['group']['key'] === $group['key']) {
                if ($condition !== '' && $existing['condition'] !== '' && $existing['condition'] !== $condition) {
                    $existing['condition'] .= '、または' . $condition;
                } elseif ($condition === '') {
                    $existing['condition'] = '';
                }
                return;
            }
        }
        unset($existing);
        $this->acf[$bucket][$key][] = ['group' => $group, 'condition' => $condition];
    }

    /**
     * AND ルール群 → 紐付け先（複数可）と条件文
     * 戻り値：[ ['bucket' => 'post_types'|'pages'|'options_pages', 'key' => ..., 'condition' => ...], ... ]
     */
    private function resolve_location(array $rules, array $excluded): array
    {
        $post_types  = [];
        $page_ids    = [];
        $options     = [];
        $conditions  = [];
        $skip        = false;

        foreach ($rules as $r) {
            $param = $r['param'] ?? '';
            $op    = $r['operator'] ?? '==';
            $value = (string) ($r['value'] ?? '');
            $eq    = ($op === '==');

            switch ($param) {
                case 'options_page':
                    if ($eq) {
                        $options[] = $value;
                    }
                    break;

                case 'post_type':
                    if ($eq) {
                        $post_types[] = $value;
                    } else {
                        $pto = get_post_type_object($value);
                        $conditions[] = '「' . ($pto ? $pto->labels->name : $value) . '」以外の場合';
                    }
                    break;

                case 'page':
                case 'post':
                    if ($eq && is_numeric($value)) {
                        $page_ids[] = (int) $value;
                    } elseif (!$eq) {
                        $conditions[] = '「' . get_the_title((int) $value) . '」以外の場合';
                    }
                    break;

                case 'page_type':
                    if ($value === 'front_page') {
                        $front = (int) get_option('page_on_front');
                        if ($eq && $front) {
                            $page_ids[] = $front;
                        } else {
                            $conditions[] = $eq ? 'トップページの場合' : 'トップページ以外の場合';
                        }
                    } elseif ($value === 'posts_page') {
                        $posts_page = (int) get_option('page_for_posts');
                        if ($eq && $posts_page) {
                            $page_ids[] = $posts_page;
                        } else {
                            $conditions[] = $eq ? '投稿一覧ページの場合' : '投稿一覧ページ以外の場合';
                        }
                    } elseif ($value === 'top_level') {
                        $conditions[] = $eq ? '親ページがない場合' : '親ページがある場合';
                    } elseif ($value === 'parent') {
                        $conditions[] = $eq ? '子ページを持つ場合' : '子ページを持たない場合';
                    } elseif ($value === 'child') {
                        $conditions[] = $eq ? '子ページの場合' : '子ページ以外の場合';
                    }
                    if (empty($post_types) && empty($page_ids)) {
                        $post_types[] = 'page';
                    }
                    break;

                case 'page_template':
                    $name = $value === 'default' ? 'デフォルトテンプレート' : ($this->templates[$value] ?? basename($value));
                    $conditions[] = 'テンプレート「' . $name . '」' . ($eq ? 'を使用している場合' : '以外の場合');
                    if (empty($post_types) && empty($page_ids)) {
                        $post_types[] = 'page';
                    }
                    break;

                case 'page_parent':
                    $conditions[] = '親ページが「' . get_the_title((int) $value) . '」' . ($eq ? 'の場合' : '以外の場合');
                    if (empty($post_types) && empty($page_ids)) {
                        $post_types[] = 'page';
                    }
                    break;

                case 'post_taxonomy':
                case 'post_category':
                    [$tax_slug, $term_slug] = array_pad(explode(':', $value, 2), 2, '');
                    if ($param === 'post_category') {
                        $tax_slug  = 'category';
                        $term_slug = $value;
                    }
                    $term = get_term_by('slug', $term_slug, $tax_slug);
                    $tax  = get_taxonomy($tax_slug);
                    $conditions[] = sprintf(
                        '%s「%s」が%s場合',
                        $tax ? $tax->labels->singular_name : $tax_slug,
                        $term ? $term->name : $term_slug,
                        $eq ? '選ばれている' : '選ばれていない'
                    );
                    if (empty($post_types) && empty($page_ids) && $tax) {
                        $post_types = array_merge($post_types, (array) $tax->object_type);
                    }
                    break;

                case 'post_format':
                    $conditions[] = '投稿フォーマットが「' . $value . '」' . ($eq ? 'の場合' : '以外の場合');
                    break;

                case 'post_status':
                    $conditions[] = 'ステータスが「' . $value . '」' . ($eq ? 'の場合' : '以外の場合');
                    break;

                case 'current_user':
                case 'current_user_role':
                    // 権限による出し分けはマニュアルに載せない（見えない人には見えないため）
                    break;

                // ユーザー・タクソノミー・添付・コメント・ウィジェット・メニュー・ブロックはマニュアル対象外
                case 'user_form':
                case 'user_role':
                case 'taxonomy':
                case 'attachment':
                case 'comment':
                case 'widget':
                case 'nav_menu':
                case 'nav_menu_item':
                case 'block':
                    $skip = true;
                    break;
            }
        }

        if ($skip) {
            return [];
        }

        $condition = implode('かつ', $conditions);
        $targets   = [];

        foreach ($options as $slug) {
            $targets[] = ['bucket' => 'options_pages', 'key' => $slug, 'condition' => $condition];
        }
        // 特定の記事に紐付く場合はその記事を優先（投稿タイプ側には載せない）
        if (!empty($page_ids)) {
            foreach (array_unique($page_ids) as $id) {
                $targets[] = ['bucket' => 'pages', 'key' => $id, 'condition' => $condition];
            }
            return $targets;
        }
        foreach (array_unique($post_types) as $pt) {
            if (in_array($pt, $excluded, true)) {
                continue;
            }
            $targets[] = ['bucket' => 'post_types', 'key' => $pt, 'condition' => $condition];
        }
        return $targets;
    }

    // ------------------------------------------------------------------
    // ACF：フィールド正規化
    // ------------------------------------------------------------------

    /**
     * 表示条件の文章化用に、キー → ラベル/型/選択肢 を先に登録する（サブフィールド含む）
     */
    private function index_fields(array $fields): void
    {
        foreach ($fields as $f) {
            $this->field_index[$f['key']] = [
                'label'   => $f['label'],
                'type'    => $f['type'],
                'choices' => $f['choices'] ?? [],
            ];
            if (!empty($f['sub_fields'])) {
                $this->index_fields($f['sub_fields']);
            }
            if (!empty($f['layouts'])) {
                foreach ($f['layouts'] as $layout) {
                    $this->index_fields($layout['sub_fields'] ?? []);
                }
            }
        }
    }

    private function fields(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            if (in_array($field['type'], ['tab', 'accordion', 'message'], true)) {
                continue;
            }
            $item = [
                'name'         => $field['name'],
                'label'        => $field['label'],
                'type'         => $field['type'],
                'instructions' => $field['instructions'] ?? '',
                'required'     => !empty($field['required']),
                'choices'      => $field['choices'] ?? [],
                'constraints'  => $this->constraints($field),
                'condition'    => $this->conditional_text($field),
                'sub_fields'   => [],
                'layouts'      => [],
            ];
            if (in_array($field['type'], ['repeater', 'group'], true) && !empty($field['sub_fields'])) {
                $item['sub_fields'] = $this->fields($field['sub_fields']);
            }
            if ($field['type'] === 'flexible_content' && !empty($field['layouts'])) {
                foreach ($field['layouts'] as $layout) {
                    $item['layouts'][] = [
                        'label'      => $layout['label'],
                        'sub_fields' => $this->fields($layout['sub_fields'] ?? []),
                    ];
                }
            }
            $result[] = $item;
        }
        return $result;
    }

    /**
     * 入力制約（文字数・数値範囲・ファイル形式/容量/寸法・件数）を文章化
     */
    private function constraints(array $f): array
    {
        $c    = [];
        $type = $f['type'];
        $has  = function ($k) use ($f) {
            return isset($f[$k]) && $f[$k] !== '' && $f[$k] !== null && $f[$k] !== 0 && $f[$k] !== '0';
        };

        if ($has('maxlength')) {
            $c[] = '最大' . (int) $f['maxlength'] . '文字';
        }
        if (in_array($type, ['number', 'range'], true)) {
            if ($has('min') && $has('max')) {
                $c[] = $f['min'] . '〜' . $f['max'] . 'の範囲';
            } elseif ($has('min')) {
                $c[] = $f['min'] . '以上';
            } elseif ($has('max')) {
                $c[] = $f['max'] . '以下';
            }
            if ($has('step') && $f['step'] != 1) {
                $c[] = $f['step'] . '単位';
            }
        }
        if (in_array($type, ['image', 'file', 'gallery'], true)) {
            if ($has('mime_types')) {
                $c[] = '形式：' . strtoupper(str_replace(',', ' / ', preg_replace('/\s+/', '', $f['mime_types'])));
            }
            if ($has('max_size')) {
                $c[] = '1ファイル' . $f['max_size'] . 'MBまで';
            }
            if ($has('min_size')) {
                $c[] = '1ファイル' . $f['min_size'] . 'MB以上';
            }
            if ($type !== 'file') {
                if ($has('min_width') || $has('min_height')) {
                    $c[] = sprintf('最小 横%s × 縦%s px', $has('min_width') ? $f['min_width'] : '指定なし', $has('min_height') ? $f['min_height'] : '指定なし');
                }
                if ($has('max_width') || $has('max_height')) {
                    $c[] = sprintf('最大 横%s × 縦%s px', $has('max_width') ? $f['max_width'] : '指定なし', $has('max_height') ? $f['max_height'] : '指定なし');
                }
            }
        }
        if (in_array($type, ['repeater', 'gallery', 'flexible_content', 'relationship', 'post_object', 'checkbox', 'select', 'taxonomy', 'user'], true)) {
            if ($has('min') && !in_array($type, ['number', 'range'], true)) {
                $c[] = '最低' . (int) $f['min'] . '件';
            }
            if ($has('max') && !in_array($type, ['number', 'range'], true)) {
                $c[] = '最大' . (int) $f['max'] . '件';
            }
        }
        if (in_array($type, ['select', 'post_object', 'taxonomy', 'user'], true) && !empty($f['multiple'])) {
            $c[] = '複数選択可';
        }
        if (in_array($type, ['text', 'textarea', 'number', 'email', 'url', 'select', 'radio', 'button_group'], true)
            && isset($f['default_value']) && is_scalar($f['default_value']) && (string) $f['default_value'] !== '') {
            $dv = (string) $f['default_value'];
            $c[] = '初期値：' . ($f['choices'][$dv] ?? $dv);
        }
        return $c;
    }

    /**
     * ACF の conditional_logic を「〜のとき表示されます」に文章化
     */
    private function conditional_text(array $f): string
    {
        $logic = $f['conditional_logic'] ?? null;
        if (empty($logic) || !is_array($logic)) {
            return '';
        }
        $or = [];
        foreach ($logic as $and_rules) {
            if (!is_array($and_rules)) {
                continue;
            }
            $and = [];
            foreach ($and_rules as $r) {
                $src    = $this->field_index[$r['field'] ?? ''] ?? null;
                $label  = $src ? $src['label'] : '別の項目';
                $val    = (string) ($r['value'] ?? '');
                $vlabel = ($src && isset($src['choices'][$val])) ? (string) $src['choices'][$val] : $val;
                $is_tf  = $src && $src['type'] === 'true_false';

                switch ($r['operator'] ?? '==') {
                    case '==':
                        $and[] = $is_tf
                            ? sprintf('「%s」が%s', $label, $val === '1' ? 'ON' : 'OFF')
                            : sprintf('「%s」が「%s」', $label, $vlabel);
                        break;
                    case '!=':
                        $and[] = $is_tf
                            ? sprintf('「%s」が%s', $label, $val === '1' ? 'OFF' : 'ON')
                            : sprintf('「%s」が「%s」以外', $label, $vlabel);
                        break;
                    case '==empty':
                        $and[] = sprintf('「%s」が未入力', $label);
                        break;
                    case '!=empty':
                        $and[] = sprintf('「%s」に入力がある', $label);
                        break;
                    case '==contains':
                        $and[] = sprintf('「%s」に「%s」を含む', $label, $val);
                        break;
                    case '==pattern':
                        $and[] = sprintf('「%s」が指定の形式', $label);
                        break;
                    case '>':
                        $and[] = sprintf('「%s」が%sより大きい', $label, $val);
                        break;
                    case '<':
                        $and[] = sprintf('「%s」が%sより小さい', $label, $val);
                        break;
                }
            }
            if ($and) {
                $or[] = implode('、かつ', $and);
            }
        }
        if (!$or) {
            return '';
        }
        return implode('／または ', $or) . ' のときに表示されます';
    }

    // ------------------------------------------------------------------
    // オプションページ / メニュー / プラグイン
    // ------------------------------------------------------------------

    private function options_pages(): array
    {
        if (!function_exists('acf_get_options_pages')) {
            return [];
        }
        $pages = acf_get_options_pages();
        if (!$pages) {
            return [];
        }
        $result = [];
        foreach ($pages as $page) {
            $groups = $this->acf['options_pages'][$page['menu_slug']] ?? [];
            if (empty($groups) && empty($page['parent_slug']) && $this->has_child_page($pages, $page['menu_slug'])) {
                continue;
            }
            $result[] = [
                'slug'         => $page['menu_slug'],
                'title'        => $page['page_title'],
                'menu_title'   => $page['menu_title'],
                'parent'       => $page['parent_slug'] ?? '',
                'field_groups' => $groups,
                'url'          => admin_url('admin.php?page=' . $page['menu_slug']),
            ];
        }
        return $result;
    }

    private function has_child_page(array $pages, string $slug): bool
    {
        foreach ($pages as $p) {
            if (($p['parent_slug'] ?? '') === $slug) {
                return true;
            }
        }
        return false;
    }

    private function menus(): array
    {
        $locations = get_registered_nav_menus();
        $assigned  = get_nav_menu_locations();
        $result    = [];
        foreach ($locations as $location => $desc) {
            $menu = isset($assigned[$location]) ? wp_get_nav_menu_object($assigned[$location]) : null;
            $result[] = [
                'location' => $location,
                'label'    => $desc,
                'menu'     => $menu ? $menu->name : '',
            ];
        }
        return $result;
    }

    private function plugins(): array
    {
        return [
            'acf'         => function_exists('acf_get_field_groups'),
            'acf_pro'     => defined('ACF_PRO'),
            'welcart'     => class_exists('usces') || defined('USCES_VERSION'),
            'woocommerce' => class_exists('WooCommerce'),
            'mw_wp_form'  => class_exists('MW_WP_Form') || defined('MWF_PLUGIN_DIR'),
            'yoast'       => defined('WPSEO_VERSION'),
            'ssp'         => defined('SSP_VERSION'),
            'scf'         => class_exists('SCF'),
        ];
    }
}
