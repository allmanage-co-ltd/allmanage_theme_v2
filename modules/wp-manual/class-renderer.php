<?php

/**
 * 解析結果 → マニュアルHTML
 *
 * セクション配列（id / title / html）を返し、テンプレート側で目次と本文を描画する。
 * 文言はフィルタ wp_manual/field_type_guide で差し替え可能。
 */

namespace WpManual;

if (!defined('ABSPATH')) {
    exit;
}

class Renderer
{
    /** @var array 解析結果 */
    private $info;

    /** @var array 補足メモ（section_id => text） */
    private $notes;

    /** @var array セクションID => 'visible' | 'hidden'（管理者が明示的に切り替えたもの） */
    private $visibility;

    /** 管理者が表示に切り替えるまで編集者には出さないセクション（固定ページ全般。閲覧ログは常に管理者のみ） */
    const DEFAULT_HIDDEN = ['pt-page', 'page-*'];

    /** @var array FAQ（id => ['q' => ..., 'a' => ...]） */
    private $faq;

    /** @var bool 補足メモ・FAQを編集できるか（編集者以上） */
    private $can_note;

    /** @var bool 表示/非表示・問い合わせ先を編集できるか（管理者） */
    private $is_admin;

    /** @var array 閲覧ログ集計（管理者のみ渡される） */
    private $log;

    public function __construct(array $info, array $notes, array $visibility, array $faq, array $log, bool $can_note, bool $is_admin)
    {
        $this->info       = $info;
        $this->notes      = $notes;
        $this->visibility = $visibility;
        $this->faq        = $faq;
        $this->log        = $log;
        $this->can_note   = $can_note;
        $this->is_admin   = $is_admin;
    }

    /**
     * 全セクションを返す
     */
    public function sections(): array
    {
        $sections = [];

        $sections[] = $this->section('intro', 'はじめに', 'basic', 'dashicons-info-outline', $this->intro_html());
        $sections[] = $this->section('login', 'ログイン・画面の見方', 'basic', 'dashicons-admin-users', $this->login_html());
        $sections[] = $this->section('media', '画像・ファイル', 'basic', 'dashicons-format-image', $this->media_html());

        foreach ($this->info['post_types'] as $pt) {
            $sections[] = $this->section('pt-' . $pt['slug'], $pt['label'], 'content', 'dashicons-edit', $this->post_type_html($pt));
        }
        foreach ($this->info['pages'] as $page) {
            $sections[] = $this->section('page-' . $page['id'], $page['title'], 'content', 'dashicons-admin-page', $this->page_html($page));
        }

        foreach ($this->info['options_pages'] as $page) {
            $sections[] = $this->section('op-' . $page['slug'], $page['menu_title'], 'settings', 'dashicons-admin-generic', $this->options_page_html($page));
        }
        if (!empty($this->info['menus'])) {
            $sections[] = $this->section('menus', 'ナビゲーションメニュー', 'settings', 'dashicons-menu', $this->menus_html());
        }

        foreach ($this->info['inquiries'] as $inq) {
            $sections[] = $this->section('inq-' . $inq['slug'], $inq['label'], 'inquiry', 'dashicons-email-alt', $this->inquiry_html($inq));
        }

        if ($this->info['plugins']['welcart']) {
            $sections[] = $this->section('welcart', 'ネットショップ', 'shop', 'dashicons-cart', $this->welcart_html());
        }

        $sections[] = $this->section('faq', '困ったときは', 'support', 'dashicons-sos', $this->faq_html());
        if ($this->is_admin) {
            // ここまでに組んだセクションからタイトルを解決する（ログは常に最後）
            $sections[] = $this->section('log', '閲覧ログ', 'admin', 'dashicons-chart-bar', $this->log_html($sections));
        }

        return apply_filters('wp_manual/sections', $sections, $this->info);
    }

    /**
     * タブのグループ定義（表示順）
     */
    public static function groups(): array
    {
        return [
            'basic'    => '基本操作',
            'content'  => 'コンテンツの更新',
            'settings' => 'サイト設定',
            'inquiry'  => 'お問い合わせ履歴',
            'shop'     => 'ショップ',
            'support'  => '困ったときは',
            'admin'    => '管理者向け',
        ];
    }

    private function section(string $id, string $title, string $group, string $icon, string $html): array
    {
        return [
            'id'     => $id,
            'title'  => $title,
            'group'  => $group,
            'icon'   => $icon,
            'hidden' => $this->is_hidden($id),
            'html'   => $html . $this->note_html($id),
        ];
    }

    /**
     * 明示設定があればそれを、無ければデフォルト（DEFAULT_HIDDEN に含まれるか）で判定
     */
    public function is_hidden(string $id): bool
    {
        if (isset($this->visibility[$id])) {
            return $this->visibility[$id] === 'hidden';
        }
        return in_array($id, self::DEFAULT_HIDDEN, true) || strpos($id, 'page-') === 0;
    }

    // ------------------------------------------------------------------
    // 各セクション
    // ------------------------------------------------------------------

    private function intro_html(): string
    {
        $site = $this->info['site'];
        $pts  = array_map(function ($pt) {
            return sprintf('<a href="#pt-%s" class="wpm-tabLink">%s</a>', esc_attr($pt['slug']), esc_html($pt['label']));
        }, $this->info['post_types']);

        $html  = '<p>このマニュアルは「' . esc_html($site['name']) . '」の管理画面を操作するための手順書です。';
        $html .= 'サイトの構成から自動生成しているため、常に現在の設定に合った内容が表示されます。</p>';
        $html .= '<h4>このサイトで更新できるコンテンツ</h4><p class="wpm-muted">左のタブから各項目の手順を開けます。</p>';
        foreach ($this->info['pages'] as $page) {
            $pts[] = sprintf('<a href="#page-%d" class="wpm-tabLink">%s</a><span class="wpm-muted">（専用項目あり）</span>', $page['id'], esc_html($page['title']));
        }
        $html .= '<ul class="wpm-list">' . implode('', array_map(function ($a) {
            return '<li>' . $a . '</li>';
        }, $pts)) . '</ul>';

        if (!empty($this->info['options_pages'])) {
            $html .= '<h4>サイト全体の設定</h4><ul class="wpm-list">';
            foreach ($this->info['options_pages'] as $page) {
                $html .= sprintf('<li><a href="#op-%s" class="wpm-tabLink">%s</a></li>', esc_attr($page['slug']), esc_html($page['menu_title']));
            }
            $html .= '</ul>';
        }
        return $html;
    }

    private function login_html(): string
    {
        $site = $this->info['site'];
        $html  = $this->steps([
            ['ログイン画面を開く', sprintf('<code>%s</code> にアクセスします。', esc_html(wp_login_url()))],
            ['ユーザー名とパスワードを入力', 'お渡ししているアカウント情報を入力し「ログイン」を押します。パスワードを忘れた場合は「パスワードをお忘れですか？」から再設定できます。'],
            ['ダッシュボードが表示される', '左側の黒いメニューから各機能に移動します。以降の手順は、この左メニューの項目名で案内します。'],
        ]);
        $html .= $this->callout('サイトを見る', '画面上部の黒いバーにあるサイト名をクリックすると、公開中のサイトを確認できます。編集中の内容は「公開」または「更新」を押すまで反映されません。');
        return $html;
    }

    private function post_type_html(array $pt): string
    {
        $s = $pt['singular'];
        $html = '';

        // 概要
        $badges = [];
        $badges[] = $this->badge($pt['count'] . '件公開中');
        if ($pt['editor'] === 'block') {
            $badges[] = $this->badge('ブロックエディター');
        } elseif ($pt['editor'] === 'classic') {
            $badges[] = $this->badge('クラシックエディター');
        }
        if ($pt['hierarchical']) {
            $badges[] = $this->badge('親子構造あり');
        }
        $html .= '<p class="wpm-badges">' . implode('', $badges) . '</p>';
        if ($pt['description'] !== '') {
            $html .= '<p>' . esc_html($pt['description']) . '</p>';
        }

        // 手順
        $steps = [];
        $steps[] = ['一覧を開く', sprintf('左メニューの「%s」をクリックします。登録済みの%sが一覧で表示されます。%s', esc_html($pt['label']), esc_html($s), $this->link_btn($pt['urls']['list'], '一覧を開く'))];
        $steps[] = ['新規追加', sprintf('一覧上部の「新規追加」を押すと入力画面が開きます。既存の%sを直したい場合は、一覧でタイトルをクリックします。%s', esc_html($s), $this->link_btn($pt['urls']['new'], '新規追加画面を開く'))];
        $steps[] = ['内容を入力', '下の「入力項目」を参考に、各項目を入力します。<strong>必須</strong>と書かれた項目は空欄のままだと保存できません。'];
        if (!empty($pt['taxonomies'])) {
            $labels = array_map(function ($t) { return esc_html($t['label']); }, $pt['taxonomies']);
            $steps[] = ['分類を選ぶ', sprintf('右側（または下部）の「%s」で該当する項目にチェックを入れます。', implode('」「', $labels))];
        }
        if (in_array('thumbnail', $pt['supports'], true)) {
            $steps[] = ['アイキャッチ画像を設定', '右側の「アイキャッチ画像」→「アイキャッチ画像を設定」から、一覧やトップページに表示される画像を選びます。'];
        }
        $steps[] = ['公開する', '右上の「公開」（既存の場合は「更新」）を押します。すぐに公開したくない場合は「公開日時」を未来の日時にすると予約投稿になります。下書きのまま保存するには「下書き保存」を押します。'];
        $html .= '<h4>更新の流れ</h4>' . $this->steps($steps);

        // 入力項目
        $html .= '<h4>入力項目</h4>';
        $rows = [];
        if (in_array('title', $pt['supports'], true)) {
            $rows[] = ['タイトル', 'テキスト', '一覧や見出しに表示される名称です。', true];
        }
        if ($pt['editor'] !== 'none') {
            $rows[] = ['本文', $pt['editor'] === 'block' ? 'ブロックエディター' : 'エディター', $pt['editor'] === 'block'
                ? '「＋」ボタンで段落・見出し・画像などのブロックを追加して本文を組み立てます。'
                : 'Wordのような感覚で文章を入力します。ツールバーから見出し・太字・リンク・画像挿入ができます。', false];
        }
        if (in_array('excerpt', $pt['supports'], true)) {
            $rows[] = ['抜粋', 'テキスト', '一覧などに表示される短い紹介文です。空欄の場合は本文の冒頭が使われます。', false];
        }
        if (in_array('thumbnail', $pt['supports'], true)) {
            $rows[] = ['アイキャッチ画像', '画像', '一覧・トップページ等に表示される代表画像です。', false];
        }
        $html .= $this->fields_table($rows, $pt['field_groups']);

        // タクソノミー
        if (!empty($pt['taxonomies'])) {
            $html .= '<h4>分類の管理</h4><ul class="wpm-list">';
            foreach ($pt['taxonomies'] as $tax) {
                $html .= sprintf(
                    '<li><strong>%s</strong>：%s %s</li>',
                    esc_html($tax['label']),
                    $tax['hierarchical'] ? '親子関係を持てる分類です。新しい項目の追加・名称変更は専用画面で行います。' : 'タグのように自由に付けられる分類です。入力欄に直接打ち込んで追加できます。',
                    $this->link_btn($tax['url'], $tax['label'] . 'を管理')
                );
            }
            $html .= '</ul>';
        }

        return $html;
    }

    /**
     * ACF が特定の固定ページ / 記事に紐付いている場合の個別セクション
     */
    private function page_html(array $page): string
    {
        $html  = '<p class="wpm-badges">' . $this->badge($page['type_label']) . $this->badge('このページ専用の入力項目あり') . '</p>';
        $html .= '<p>「' . esc_html($page['title']) . '」には、このページだけで使う専用の入力項目があります。通常の本文とは別に、以下の項目を編集画面で入力します。</p>';
        $html .= $this->steps([
            ['編集画面を開く', sprintf('左メニューの「%s」一覧から「%s」を開きます。%s', esc_html($page['type_label']), esc_html($page['title']), $this->link_btn($page['url'], '編集画面を開く'))],
            ['専用項目を入力', '本文の下（またはタブ）に表示される専用項目を、下の一覧を参考に入力します。'],
            ['更新', '右上の「更新」を押すと反映されます。' . $this->link_btn($page['view_url'], '公開ページを確認')],
        ]);
        $html .= '<h4>このページ専用の入力項目</h4>' . $this->fields_table([], $page['field_groups']);
        return $html;
    }

    private function options_page_html(array $page): string
    {
        $html  = '<p>サイト全体に関わる設定です。ここで変更した内容は、該当する全ページに反映されます。</p>';
        $html .= $this->steps([
            ['設定画面を開く', sprintf('左メニューの「%s」をクリックします。%s', esc_html($page['menu_title']), $this->link_btn($page['url'], '設定画面を開く'))],
            ['内容を変更', '下の「設定項目」を参考に変更します。'],
            ['更新する', '画面の「更新」ボタンを押すと保存され、すぐに反映されます。'],
        ]);
        $html .= '<h4>設定項目</h4>' . $this->fields_table([], $page['field_groups']);
        return $html;
    }

    private function media_html(): string
    {
        $html = $this->steps([
            ['メディアライブラリを開く', '左メニューの「メディア」で、アップロード済みの画像・PDFを一覧できます。' . $this->link_btn(admin_url('upload.php'), 'メディアを開く')],
            ['アップロード', '「新規追加」を押し、ファイルをドラッグ＆ドロップするか「ファイルを選択」から選びます。各記事の入力画面からも直接アップロードできます。'],
            ['差し替え・削除', '一覧で画像をクリックすると詳細が開きます。不要になった画像は「完全に削除する」で削除できます。記事で使用中の画像を削除すると、その記事の画像が表示されなくなるので注意してください。'],
        ]);
        $html .= $this->callout('画像の推奨', 'ファイル名は半角英数字にしてください（日本語ファイル名は文字化けの原因になります）。写真は横幅1200〜2000px程度、1枚あたり500KB以下を目安にすると表示が速くなります。');
        return $html;
    }

    private function menus_html(): string
    {
        $html  = '<p>サイト上部のナビゲーションやフッターのリンクは「外観」→「メニュー」で管理します。' . $this->link_btn(admin_url('nav-menus.php'), 'メニューを開く') . '</p>';
        $html .= '<table class="wpm-table"><thead><tr><th>表示位置</th><th>設定されているメニュー</th></tr></thead><tbody>';
        foreach ($this->info['menus'] as $m) {
            $html .= sprintf('<tr><td>%s</td><td>%s</td></tr>', esc_html($m['label']), $m['menu'] !== '' ? esc_html($m['menu']) : '<span class="wpm-muted">未設定</span>');
        }
        $html .= '</tbody></table>';
        $html .= $this->steps([
            ['編集するメニューを選ぶ', '画面上部の「編集するメニューを選択」で対象を選び「選択」を押します。'],
            ['項目を追加', '左側の「固定ページ」「投稿」「カスタムリンク」から追加したいものにチェックを入れ「メニューに追加」を押します。'],
            ['並び替え・保存', '右側の項目をドラッグして順番を変え、右にずらすと子メニュー（ドロップダウン）になります。最後に「メニューを保存」を押します。'],
        ]);
        return $html;
    }

    private function welcart_html(): string
    {
        $html  = '<p>ネットショップ機能は「Welcart」で運用しています。主な作業は商品の登録と受注の確認です。</p>';
        $html .= '<h4>商品の登録・変更</h4>' . $this->steps([
            ['商品マスターを開く', '左メニューの「Welcart Shop」→「商品マスター」を開きます。' . $this->link_btn(admin_url('admin.php?page=usces_itemedit'), '商品マスターを開く')],
            ['新規商品追加', '「新規商品追加」から、商品名・商品コード・価格・在庫・説明・画像を登録します。'],
            ['SKU（規格）', 'サイズや色ごとに価格・在庫を分ける場合は「SKU」を追加します。1商品に最低1つのSKUが必要です。'],
            ['公開', '「公開」を押すとショップに表示されます。「非公開」にすると一時的に販売停止できます。'],
        ]);
        $html .= '<h4>受注の確認</h4>' . $this->steps([
            ['受注リストを開く', '「Welcart Shop」→「受注リスト」で注文を確認します。新着順に並んでいます。' . $this->link_btn(admin_url('admin.php?page=usces_orderlist'), '受注リストを開く')],
            ['対応状況の更新', '注文を開き「対応状況」を「新規受付」→「発送済み」などに変更して「更新」を押します。'],
        ]);
        $html .= $this->callout('注意', '商品コード（SKUコード）は一度決めたら変更しないでください。受注データとの紐付けが切れる原因になります。');
        return $html;
    }

    /**
     * MW WP Form の問い合わせ履歴
     */
    private function inquiry_html(array $inq): string
    {
        $html  = '<p class="wpm-badges">' . $this->badge($inq['count'] . '件') . $this->badge('閲覧・CSV出力のみ') . '</p>';
        $html .= '<p>フォーム「' . esc_html($inq['label']) . '」から送信された内容が自動で保存されています。';
        $html .= 'ここは<strong>受信した問い合わせを確認する場所</strong>で、記事のように新しく作成・公開するものではありません。</p>';
        $html .= '<h4>受信内容の確認</h4>' . $this->steps([
            ['一覧を開く', sprintf('左メニューの「%s」を開くと、受信日時の新しい順に一覧表示されます。%s', esc_html($inq['label']), $this->link_btn($inq['url'], '一覧を開く'))],
            ['内容を見る', '一覧のタイトル（受信日時や氏名）をクリックすると、送信された全項目が表示されます。内容は閲覧専用です。'],
            ['対応状況をメモ', '詳細画面の「メモ」欄に対応内容を残しておくと、担当者間で共有できます。'],
        ]);
        $html .= '<h4>CSVでダウンロード</h4>' . $this->steps([
            ['期間で絞り込む', '一覧上部の日付フィルターで対象期間を選び「絞り込み」を押します（絞り込まない場合は全件が対象）。'],
            ['CSVダウンロード', '一覧の下部（または上部）にある「CSVダウンロード」ボタンを押すと、表示中の一覧がCSVで保存されます。Excel で開いて集計できます。'],
        ]);
        $html .= $this->callout('個人情報の取り扱い', 'ダウンロードしたCSVには氏名・連絡先などの個人情報が含まれます。共有フォルダやメールでの受け渡しは社内ルールに従い、不要になったら削除してください。');
        return $html;
    }

    /**
     * FAQ（編集者以上は追加・編集・削除できる）
     */
    private function faq_html(): string
    {
        $html = '';

        if (empty($this->faq)) {
            $html .= '<p class="wpm-muted">まだ項目がありません。</p>';
        } else {
            $html .= '<dl class="wpm-faq">';
            foreach ($this->faq as $id => $item) {
                $html .= '<div class="wpm-faq__item">';
                $html .= '<dt>' . esc_html($item['q']) . '</dt>';
                $html .= '<dd>' . wpautop(esc_html($item['a'])) . '</dd>';
                if ($this->can_note) {
                    $html .= $this->faq_edit_form($id, $item);
                }
                $html .= '</div>';
            }
            $html .= '</dl>';
        }

        if ($this->can_note) {
            $html .= '<details class="wpm-editBox wpm-editBox--add"><summary><span class="dashicons dashicons-plus-alt2"></span> 質問を追加</summary>';
            $html .= $this->faq_edit_form('', ['q' => '', 'a' => '']);
            $html .= '</details>';
        }
        return $html;
    }

    private function faq_edit_form(string $id, array $item): string
    {
        $is_new = $id === '';
        $html  = '<div class="wpm-faq__edit">';
        if (!$is_new) {
            $html .= '<details class="wpm-editBox"><summary><span class="dashicons dashicons-edit"></span> この質問を編集</summary>';
        }
        $html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="wpm-form">';
        $html .= wp_nonce_field('wp_manual_faq', '_wpnonce', true, false);
        $html .= '<input type="hidden" name="action" value="wp_manual_save_faq">';
        $html .= '<input type="hidden" name="faq_id" value="' . esc_attr($id) . '">';
        $html .= '<label>質問<input type="text" name="q" class="large-text" value="' . esc_attr($item['q']) . '" required placeholder="例）更新したのに反映されない"></label>';
        $html .= '<label>回答<textarea name="a" rows="4" class="large-text" required placeholder="回答を入力">' . esc_textarea($item['a']) . '</textarea></label>';
        $html .= '<div class="wpm-form__actions">';
        $html .= '<button type="submit" class="button button-primary">' . ($is_new ? '追加する' : '保存') . '</button>';
        if (!$is_new) {
            $html .= '<button type="submit" class="button button-link-delete" name="delete" value="1" onclick="return confirm(\'この質問を削除します。よろしいですか？\');">削除</button>';
        }
        $html .= '</div></form>';
        if (!$is_new) {
            $html .= '</details>';
        }
        return $html . '</div>';
    }

    /**
     * FAQ の初期値（オプション未作成時に1度だけ保存される）
     */
    public static function default_faq(): array
    {
        return [
            'faq_' . wp_generate_password(6, false) => ['q' => '更新したのにサイトに反映されない', 'a' => "ブラウザのキャッシュが残っている可能性があります。Ctrl+F5（Macは Cmd+Shift+R）で再読み込みしてください。\nそれでも反映されない場合は「公開」ではなく「下書き保存」になっていないか確認してください。"],
            'faq_' . wp_generate_password(6, false) => ['q' => '間違えて公開してしまった', 'a' => '該当記事を開き、右側の「ステータス」を「下書き」に変更して「更新」を押すと非公開に戻せます。'],
            'faq_' . wp_generate_password(6, false) => ['q' => '削除した記事を戻したい', 'a' => '一覧上部の「ゴミ箱」を開き、対象の「復元」を押します。ゴミ箱は30日で自動的に空になります。'],
            'faq_' . wp_generate_password(6, false) => ['q' => '画像がアップロードできない', 'a' => 'ファイルサイズが大きすぎる（数十MB以上）か、対応していない形式の可能性があります。JPG / PNG / GIF / PDF が基本です。'],
            'faq_' . wp_generate_password(6, false) => ['q' => 'それ以外で困ったとき', 'a' => '操作を止めて、画面のスクリーンショットを添えて担当者までご連絡ください。'],
        ];
    }

    // ------------------------------------------------------------------
    // ACF フィールド → 説明
    // ------------------------------------------------------------------

    /**
     * 標準項目 + ACF フィールドグループを1つの表にする
     * $rows: [label, type_label, howto, required]
     */
    private function fields_table(array $rows, array $groups): string
    {
        // 標準項目を先に並べる（5番目=階層, 6番目=グループ見出し行フラグ）
        $rows = array_map(function ($r) {
            return [$r[0], $r[1], $r[2], $r[3], 0, false];
        }, $rows);

        foreach ($groups as $entry) {
            $group     = $entry['group'];
            $condition = $entry['condition'] ?? '';
            if (count($groups) > 1 || $condition !== '') {
                $heading = esc_html($group['title']);
                if ($condition !== '') {
                    $heading .= ' <span class="wpm-cond"><span class="dashicons dashicons-randomize"></span>' . esc_html($condition) . 'のみ表示</span>';
                }
                $rows[] = [$heading, '', '', false, 0, true];
            }
            foreach ($group['fields'] as $field) {
                $rows = array_merge($rows, $this->field_rows($field, 0));
            }
        }
        if (empty($rows)) {
            return '<p class="wpm-muted">この画面に特別な入力項目はありません。</p>';
        }

        $html = '<table class="wpm-table wpm-table--fields"><thead><tr><th>項目名</th><th>種類</th><th>入力方法・ルール</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            [$label, $type, $howto, $required, $depth, $is_heading] = $r;
            if ($is_heading) {
                $html .= '<tr class="wpm-groupRow"><th colspan="3">' . $label . '</th></tr>';
                continue;
            }
            $html .= sprintf(
                '<tr class="%s"><td>%s%s%s</td><td>%s</td><td>%s</td></tr>',
                $depth > 0 ? 'wpm-sub' : '',
                str_repeat('<span class="wpm-indent"></span>', $depth),
                esc_html($label),
                $required ? ' <span class="wpm-req">必須</span>' : '',
                esc_html($type),
                $howto
            );
        }
        return $html . '</tbody></table>';
    }

    /**
     * 1フィールドを行に変換（サブフィールドは階層を下げて続ける）
     */
    private function field_rows(array $field, int $depth): array
    {
        $guide = $this->field_type_guide($field['type']);
        $howto = '<span class="wpm-howto">' . $guide['howto'] . '</span>';

        if (!empty($field['choices']) && in_array($field['type'], ['select', 'radio', 'checkbox', 'button_group'], true)) {
            $howto .= '<br><span class="wpm-muted">選択肢：' . esc_html(implode(' / ', array_values($field['choices']))) . '</span>';
        }
        if (!empty($field['constraints'])) {
            $howto .= '<br><span class="wpm-rule"><span class="dashicons dashicons-shield-alt"></span>' . esc_html(implode('　', $field['constraints'])) . '</span>';
        }
        if ($field['instructions'] !== '') {
            $howto .= '<br><span class="wpm-instr">' . nl2br(esc_html($field['instructions'])) . '</span>';
        } elseif ($this->is_admin && !in_array($field['type'], ['repeater', 'group', 'flexible_content'], true)) {
            $howto .= '<br><span class="wpm-missing" title="ACF のフィールド設定「説明」が空です">説明が未記入（管理者のみ表示）</span>';
        }
        if ($field['condition'] !== '') {
            $howto .= '<br><span class="wpm-cond"><span class="dashicons dashicons-randomize"></span>' . esc_html($field['condition']) . '</span>';
        }

        $rows = [[$field['label'], $guide['label'], $howto, $field['required'], $depth, false]];

        foreach ($field['sub_fields'] as $sub) {
            $rows = array_merge($rows, $this->field_rows($sub, $depth + 1));
        }
        foreach ($field['layouts'] as $layout) {
            $rows[] = ['レイアウト：' . $layout['label'], 'レイアウト', 'このレイアウトを追加すると、以下の項目が入力できます。', false, $depth + 1, false];
            foreach ($layout['sub_fields'] as $sub) {
                $rows = array_merge($rows, $this->field_rows($sub, $depth + 2));
            }
        }
        return $rows;
    }

    /**
     * ACF フィールド型ごとの日本語ラベルと操作説明
     */
    private function field_type_guide(string $type): array
    {
        $guides = [
            'text'             => ['テキスト', '1行の文字を入力します。'],
            'textarea'         => ['テキスト（複数行）', '複数行の文章を入力します。改行はそのまま反映されます。'],
            'number'           => ['数値', '半角数字で入力します。'],
            'range'            => ['スライダー', 'つまみを動かして値を選びます。'],
            'email'            => ['メールアドレス', 'メールアドレスの形式で入力します。'],
            'url'              => ['URL', '<code>https://</code> から始まるURLを入力します。'],
            'password'         => ['パスワード', '入力内容は伏字で表示されます。'],
            'wysiwyg'          => ['エディター', 'ツールバーから見出し・太字・リンク・画像挿入ができます。本文と同じ操作感です。'],
            'oembed'           => ['埋め込み', 'YouTube などのURLを貼り付けると、動画やSNS投稿が埋め込まれます。'],
            'image'            => ['画像', '「画像を追加」→ メディアライブラリから選択、または新しくアップロードします。差し替えは画像をクリックして選び直します。'],
            'file'             => ['ファイル', '「ファイルを追加」から PDF などを選択します。ファイル名は半角英数字を推奨します。'],
            'gallery'          => ['ギャラリー（複数画像）', '「ギャラリーに追加」で複数枚を選べます。画像をドラッグして表示順を変更できます。'],
            'select'           => ['選択（プルダウン）', '一覧から1つ選びます。'],
            'checkbox'         => ['チェックボックス', '該当するものすべてにチェックを入れます。'],
            'radio'            => ['ラジオボタン', '1つだけ選びます。'],
            'button_group'     => ['ボタン選択', 'ボタンを押して1つ選びます。'],
            'true_false'       => ['ON / OFF', 'スイッチを切り替えます。ONにした場合の動作は「入力方法・ルール」の補足を確認してください。'],
            'link'             => ['リンク', '「リンクを選択」からURL・表示テキスト・新しいタブで開くかを設定します。サイト内のページは検索して選べます。'],
            'post_object'      => ['記事の選択', 'サイト内の記事を検索して1つ選びます。'],
            'page_link'        => ['ページリンク', 'サイト内のページを選ぶと、そのページへのリンクになります。'],
            'relationship'     => ['関連記事', '左の一覧から記事をクリックして右に追加します。右側でドラッグすると順番を変えられます。'],
            'taxonomy'         => ['分類', '該当する分類を選びます。'],
            'user'             => ['ユーザー', '登録ユーザーから選びます。'],
            'google_map'       => ['地図', '住所を検索して地図上の位置を指定します。'],
            'date_picker'      => ['日付', 'カレンダーから日付を選びます。'],
            'date_time_picker' => ['日時', 'カレンダーから日付と時刻を選びます。'],
            'time_picker'      => ['時刻', '時刻を選びます。'],
            'color_picker'     => ['色', 'カラーパレットから色を選ぶか、カラーコードを入力します。'],
            'repeater'         => ['繰り返し', '「行を追加」で同じ形式の項目を何件でも追加できます。行の左端をドラッグして順番を変更、右端の「−」で削除します。'],
            'group'            => ['グループ', '関連する項目がまとまっています。それぞれ入力します。'],
            'flexible_content' => ['柔軟コンテンツ', '「レイアウトを追加」から使いたい種類を選んでブロックを積み重ねます。ドラッグで順番を変更できます。'],
            'clone'            => ['複製フィールド', '別の場所で定義された項目がここに表示されます。'],
        ];
        $guide = $guides[$type] ?? [$type, '項目の指示に従って入力します。'];
        $guide = apply_filters('wp_manual/field_type_guide', ['label' => $guide[0], 'howto' => $guide[1]], $type);
        return $guide;
    }

    // ------------------------------------------------------------------
    // 閲覧ログ
    // ------------------------------------------------------------------

    private function log_html(array $built_sections): string
    {
        $summary = $this->log['summary'] ?? [];
        $recent  = $this->log['recent'] ?? [];
        $titles  = [];
        foreach ($built_sections as $bs) {
            $titles[$bs['id']] = $bs['title'];
        }

        $html  = '<p>マニュアルのどの項目がどれだけ見られているかの集計です。<strong>編集者</strong>の列は管理者以外（お客様側）の閲覧数で、自走できているか・どこを充実させるべきかの判断材料になります。</p>';

        if (empty($summary)) {
            $html .= '<p class="wpm-muted">まだ閲覧記録がありません。</p>';
        } else {
            uasort($summary, function ($a, $b) {
                return $b['recent'] <=> $a['recent'];
            });
            $html .= '<h4>項目別（直近30日）</h4>';
            $html .= '<table class="wpm-table wpm-table--log"><thead><tr><th>項目</th><th class="num">30日</th><th class="num">うち編集者</th><th class="num">累計</th><th>最終閲覧</th></tr></thead><tbody>';
            $max = max(1, max(array_column($summary, 'recent')));
            foreach ($summary as $section => $row) {
                $title = $titles[$section] ?? $section;
                $bar   = (int) round($row['recent'] / $max * 100);
                $html .= sprintf(
                    '<tr><td><a href="#%s" class="wpm-tabLink">%s</a><span class="wpm-bar" style="width:%d%%"></span></td><td class="num">%d</td><td class="num">%d</td><td class="num">%d</td><td>%s</td></tr>',
                    esc_attr($section),
                    esc_html($title),
                    $bar,
                    $row['recent'],
                    $row['recent_editor'],
                    $row['total'],
                    $row['last'] ? esc_html(wp_date('Y/m/d H:i', strtotime($row['last']))) : '－'
                );
            }
            $html .= '</tbody></table>';
        }

        if (!empty($recent)) {
            $html .= '<h4>最近の閲覧</h4>';
            $html .= '<table class="wpm-table wpm-table--log"><thead><tr><th>日時</th><th>ユーザー</th><th>項目</th></tr></thead><tbody>';
            foreach ($recent as $r) {
                $user = get_userdata((int) $r['user_id']);
                $html .= sprintf(
                    '<tr><td>%s</td><td>%s%s</td><td>%s</td></tr>',
                    esc_html(wp_date('Y/m/d H:i', strtotime($r['viewed_at']))),
                    $user ? esc_html($user->display_name) : '（削除済み）',
                    $r['is_admin'] ? ' <span class="wpm-badge">管理者</span>' : '',
                    esc_html($titles[$r['section']] ?? $r['section'])
                );
            }
            $html .= '</tbody></table>';
        }

        $html .= '<p class="wpm-muted">ログは365日で自動削除されます。</p>';
        return $html;
    }

    // ------------------------------------------------------------------
    // 部品
    // ------------------------------------------------------------------

    private function steps(array $steps): string
    {
        $html = '<ol class="wpm-steps">';
        foreach ($steps as $s) {
            $html .= sprintf('<li><strong>%s</strong><p>%s</p></li>', esc_html($s[0]), $s[1]);
        }
        return $html . '</ol>';
    }

    private function callout(string $title, string $body): string
    {
        return sprintf('<div class="wpm-callout"><strong>%s</strong><p>%s</p></div>', esc_html($title), esc_html($body));
    }

    private function badge(string $text): string
    {
        return '<span class="wpm-badge">' . esc_html($text) . '</span>';
    }

    private function link_btn(string $url, string $label): string
    {
        // 手順を読みながら操作できるよう、管理画面へのリンクはすべて別タブで開く
        return sprintf('<a class="wpm-btn" href="%s" target="_blank" rel="noopener">%s <span class="dashicons dashicons-external"></span></a>', esc_url($url), esc_html($label));
    }

    /**
     * 補足メモ（表示 + 管理者なら編集フォーム）
     */
    private function note_html(string $section_id): string
    {
        $note = $this->notes[$section_id] ?? '';
        $html = '';

        if ($note !== '') {
            $html .= '<div class="wpm-note"><strong><span class="dashicons dashicons-flag"></span> 補足</strong>' . wpautop(esc_html($note)) . '</div>';
        }

        if ($this->can_note) {
            $html .= '<details class="wpm-editBox"><summary><span class="dashicons dashicons-edit"></span> 補足メモを' . ($note !== '' ? '編集' : '追加') . '</summary>';
            $html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="wpm-form">';
            $html .= wp_nonce_field('wp_manual_note', '_wpnonce', true, false);
            $html .= '<input type="hidden" name="action" value="wp_manual_save_note">';
            $html .= '<input type="hidden" name="section" value="' . esc_attr($section_id) . '">';
            $html .= '<textarea name="note" rows="4" class="large-text" placeholder="運用ルールや注意点など、この項目に関する補足を記載（空で保存すると削除）">' . esc_textarea($note) . '</textarea>';
            $html .= '<div class="wpm-form__actions"><button type="submit" class="button button-primary">保存</button></div>';
            $html .= '</form></details>';
        }
        return $html;
    }
}
