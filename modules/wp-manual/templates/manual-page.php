<?php

/**
 * マニュアル表示テンプレート（左：グループ分けタブ / 右：選択中セクション）
 *
 * @var array  $info
 * @var array  $sections
 * @var bool   $can_note   補足メモ・FAQを編集できるか
 * @var bool   $is_admin   表示/非表示を切り替えられるか
 * @var string $message
 */

if (!defined('ABSPATH')) {
    exit;
}

use WpManual\Renderer;

$groups  = Renderer::groups();
$grouped = [];
foreach ($sections as $s) {
    $grouped[$s['group']][] = $s;
}
$first_id     = $sections[0]['id'] ?? '';
$hidden_count = count(array_filter($sections, function ($s) { return !empty($s['hidden']); }));
?>
<div class="wrap wpm" data-first="<?= esc_attr($first_id) ?>">
  <?php // WP は .wrap 内の最初の h1 の直後に通知を移動するため、通知の着地点として見えない h1 を先頭に置く ?>
  <h1 class="screen-reader-text">管理画面マニュアル</h1>

  <?php if ($message !== ''): ?>
    <div class="notice notice-success is-dismissible"><p><?= esc_html($message) ?></p></div>
  <?php endif; ?>

  <header class="wpm-header">
    <div class="wpm-header__brand">
      <span class="wpm-header__icon dashicons dashicons-book-alt"></span>
      <div>
        <div class="wpm-header__ttl"><?= esc_html($info['site']['name']) ?></div>
        <p class="wpm-header__sub">管理画面マニュアル</p>
      </div>
    </div>
    <div class="wpm-header__side">
      <p class="wpm-header__meta">
        <?= esc_html(wp_date('Y年n月j日')) ?> 時点の設定から自動生成
        <?php if ($is_admin): ?>
          <span class="wpm-pill wpm-pill--admin">管理者モード</span>
          <?php if ($hidden_count > 0): ?>
            <span class="wpm-pill wpm-pill--hidden"><?= (int) $hidden_count ?>項目を非表示中</span>
          <?php endif; ?>
        <?php elseif ($can_note): ?>
          <span class="wpm-pill wpm-pill--editor">補足メモ・FAQを編集できます</span>
        <?php endif; ?>
      </p>
      <button type="button" class="button wpm-printBtn" onclick="document.body.classList.add('wpm-printing'); window.print(); document.body.classList.remove('wpm-printing');">
        <span class="dashicons dashicons-printer"></span> 全ページを印刷 / PDF
      </button>
    </div>
  </header>

  <div class="wpm-layout">
    <nav class="wpm-tabs" role="tablist" aria-label="マニュアル目次">
      <div class="wpm-search">
        <span class="dashicons dashicons-search"></span>
        <input type="search" class="wpm-search__input" placeholder="マニュアル内を検索" aria-label="マニュアル内を検索" autocomplete="off">
        <span class="wpm-search__count" aria-live="polite"></span>
      </div>
      <p class="wpm-search__empty" hidden>該当する項目がありません</p>
      <?php foreach ($groups as $group_key => $group_label): ?>
        <?php if (empty($grouped[$group_key])) continue; ?>
        <div class="wpm-tabs__group">
          <div class="wpm-tabs__groupTtl"><?= esc_html($group_label) ?></div>
          <?php foreach ($grouped[$group_key] as $s): ?>
            <a href="#<?= esc_attr($s['id']) ?>"
               class="wpm-tab<?= !empty($s['hidden']) ? ' is-hidden' : '' ?>"
               role="tab"
               data-tab="<?= esc_attr($s['id']) ?>"
               aria-controls="panel-<?= esc_attr($s['id']) ?>">
              <span class="dashicons <?= esc_attr($s['icon']) ?>"></span>
              <span class="wpm-tab__label"><?= esc_html($s['title']) ?></span>
              <?php if (!empty($s['hidden'])): ?>
                <span class="dashicons dashicons-hidden wpm-tab__eye" title="非表示中（管理者のみ閲覧可）"></span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>

    <main class="wpm-main">
      <?php foreach ($sections as $s): ?>
        <section id="panel-<?= esc_attr($s['id']) ?>"
                 class="wpm-panel<?= !empty($s['hidden']) ? ' is-hidden' : '' ?>"
                 role="tabpanel"
                 data-panel="<?= esc_attr($s['id']) ?>"
                 hidden>
          <div class="wpm-panel__head">
            <div>
              <span class="wpm-panel__group"><?= esc_html($groups[$s['group']] ?? '') ?></span>
              <h2 class="wpm-panel__ttl">
                <span class="dashicons <?= esc_attr($s['icon']) ?>"></span>
                <?= esc_html($s['title']) ?>
                <?php if (!empty($s['hidden'])): ?>
                  <span class="wpm-pill wpm-pill--hidden"><span class="dashicons dashicons-hidden"></span> 非表示中</span>
                <?php endif; ?>
              </h2>
            </div>
            <?php if ($is_admin): ?>
              <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>" class="wpm-panel__toggle">
                <?php wp_nonce_field('wp_manual_toggle'); ?>
                <input type="hidden" name="action" value="wp_manual_toggle">
                <input type="hidden" name="section" value="<?= esc_attr($s['id']) ?>">
                <?php if (!empty($s['hidden'])): ?>
                  <button type="submit" class="button"><span class="dashicons dashicons-visibility"></span> この項目を表示する</button>
                <?php else: ?>
                  <button type="submit" class="button"><span class="dashicons dashicons-hidden"></span> この項目を非表示にする</button>
                <?php endif; ?>
              </form>
            <?php endif; ?>
          </div>
          <?php if (!empty($s['hidden'])): ?>
            <div class="wpm-hiddenBar">この項目は管理者以外には表示されていません。編集者にも見せる場合は右上の「この項目を表示する」を押してください。</div>
          <?php endif; ?>
          <div class="wpm-panel__body">
            <?= $s['html'] ?>
          </div>
        </section>
      <?php endforeach; ?>
    </main>
  </div>
</div>
