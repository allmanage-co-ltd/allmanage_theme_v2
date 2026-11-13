<?php

/**
 * 投稿タイプ全件削除ツールの管理画面ビュー
 *
 * @var string                                                                            $post_type
 * @var string                                                                            $label
 * @var int                                                                               $total
 * @var array<int, array{taxonomy: string, label: string, terms: list<array{id: int, name: string, count: int}>}> $taxonomy_terms
 * @var string                                                                            $nonce
 * @var string                                                                            $ajax_url
 * @var string                                                                            $back_url
 * @var string                                                                            $ajax_action
 */
?>

<div class="wrap">
  <div class="purge-tool">

    <div class="purge-tool__header">
      <a class="purge-back" href="<?php echo esc_url($back_url); ?>">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        一覧に戻る
      </a>
      <h1>
        <?php echo esc_html($label); ?> <span class="purge-tool__type">/ <?php echo esc_html($post_type); ?></span>
        <span class="purge-tool__badge"><?php echo number_format($total); ?> 件</span>
      </h1>
    </div>

    <div class="purge-tool__grid">

      <!-- 左：削除進捗 -->
      <section class="purge-section">
        <h2>削除進捗</h2>

        <div class="purge-progress">
          <div class="purge-progress__bar">
            <div class="purge-progress__fill" id="progress"></div>
          </div>
          <div class="purge-progress__meta">
            <span class="purge-progress__pct" id="pct">0%</span>
            <span id="rate">-</span>
          </div>
        </div>

        <div class="purge-stats">
          <div class="purge-stat">
            <span class="purge-stat__label">開始時の件数</span>
            <span class="purge-stat__value" id="total"><?php echo number_format($total); ?></span>
          </div>
          <div class="purge-stat">
            <span class="purge-stat__label">削除済み</span>
            <span class="purge-stat__value -deleted" id="deleted">0</span>
          </div>
          <div class="purge-stat">
            <span class="purge-stat__label">残り</span>
            <span class="purge-stat__value" id="remaining"><?php echo number_format($total); ?></span>
          </div>
          <div class="purge-stat">
            <span class="purge-stat__label">経過時間</span>
            <span class="purge-stat__value" id="elapsed">-</span>
          </div>
          <div class="purge-stat">
            <span class="purge-stat__label">残り時間</span>
            <span class="purge-stat__value" id="eta">-</span>
          </div>
        </div>

        <div class="purge-actions">
          <button class="purge-btn -danger" id="btn-start">削除開始</button>
          <button class="purge-btn -ghost" id="btn-stop" disabled>停止</button>
        </div>

        <p class="purge-note">※処理中はこのタブを開いたままにしてください</p>

        <div class="purge-log" id="log"></div>
      </section>

      <!-- 右：タクソノミー -->
      <section class="purge-section">
        <h2>タクソノミー</h2>

        <?php if (!empty($taxonomy_terms)) : ?>
          <p class="purge-taxonomy-note">投稿削除後に中身も消す場合はチェックを入れてください</p>

          <div class="purge-taxonomies">
            <?php foreach ($taxonomy_terms as $tax) : ?>
              <div class="purge-taxonomy">
                <label class="purge-taxonomy__head">
                  <span class="purge-checkbox">
                    <input type="checkbox" name="purge_taxonomy[]" value="<?php echo esc_attr($tax['taxonomy']); ?>" id="tax-<?php echo esc_attr($tax['taxonomy']); ?>">
                    <span class="purge-checkbox__mark"></span>
                  </span>
                  <span class="purge-taxonomy__name"><?php echo esc_html($tax['label']); ?></span>
                  <code class="purge-taxonomy__slug"><?php echo esc_html($tax['taxonomy']); ?></code>
                  <span class="purge-taxonomy__count"><?php echo count($tax['terms']); ?> ターム</span>
                </label>

                <?php if (!empty($tax['terms'])) : ?>
                  <div class="purge-terms">
                    <?php foreach ($tax['terms'] as $term) : ?>
                      <span class="purge-term">
                        <?php echo esc_html($term['name']); ?>
                        <span class="purge-term__count"><?php echo (int) $term['count']; ?></span>
                      </span>
                    <?php endforeach; ?>
                  </div>
                <?php else : ?>
                  <p class="purge-no-terms">ターム なし</p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

        <?php else : ?>
          <div class="purge-empty">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="9" stroke="#d4d4d8" stroke-width="1.5" />
              <path d="M12 8v4m0 4h.01" stroke="#d4d4d8" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            <p>紐づくタクソノミーなし</p>
          </div>
        <?php endif; ?>
      </section>

    </div>
  </div>
</div>

<script>
  (function() {
    const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
    const nonce = <?php echo wp_json_encode($nonce); ?>;
    const postType = <?php echo wp_json_encode($post_type); ?>;
    const total = <?php echo (int) $total; ?>;
    const ACTION = <?php echo wp_json_encode($ajax_action); ?>;

    const $el = (id) => document.getElementById(id);
    const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
    const fmtTime = (sec) => {
      sec = Math.round(sec);
      const h = Math.floor(sec / 3600),
        m = Math.floor(sec % 3600 / 60),
        s = sec % 60;
      return (h ? h + '時間' : '') + (h || m ? m + '分' : '') + s + '秒';
    };
    const log = (msg, type = 'info') => {
      const el = document.createElement('div');
      el.className = 'purge-log__item -' + type;
      el.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
      $el('log').appendChild(el);
      $el('log').scrollTop = $el('log').scrollHeight;
    };
    const getCheckedTaxonomies = () => [...document.querySelectorAll('input[name="purge_taxonomy[]"]:checked')].map(el => el.value);

    let running = false,
      deletedTotal = 0;

    async function run() {
      running = true;
      $el('btn-start').disabled = true;
      $el('btn-stop').disabled = false;
      const startedAt = Date.now();
      const purgeTaxonomies = getCheckedTaxonomies();

      log('削除開始' + (purgeTaxonomies.length ? '（タクソノミー対象: ' + purgeTaxonomies.join(', ') + '）' : ''));

      // 投稿削除ループ
      while (running) {
        const body = new FormData();
        body.append('action', ACTION);
        body.append('_ajax_nonce', nonce);
        body.append('post_type', postType);

        let res;
        try {
          const r = await fetch(ajaxUrl, {
            method: 'POST',
            body,
            credentials: 'same-origin'
          });
          res = await r.json();
        } catch (e) {
          log('通信エラー：5秒後に再試行（' + e.message + '）', 'error');
          await sleep(5000);
          continue;
        }

        if (!res.success) {
          log('エラー：' + (res.data?.message ?? '不明'), 'error');
          break;
        }

        const d = res.data;
        deletedTotal += d.deleted;

        const elapsedSec = (Date.now() - startedAt) / 1000;
        const rate = deletedTotal / elapsedSec;
        const pct = Math.min(100, total > 0 ? Math.round((total - d.remaining) / total * 100) : 0);

        $el('deleted').textContent = deletedTotal.toLocaleString();
        $el('remaining').textContent = d.remaining.toLocaleString();
        $el('elapsed').textContent = fmtTime(elapsedSec);
        $el('rate').textContent = Math.round(rate * 60).toLocaleString() + ' 件/分';
        $el('eta').textContent = rate > 0 ? fmtTime(d.remaining / rate) : '-';
        $el('pct').textContent = pct + '%';
        $el('progress').style.width = pct + '%';

        log(d.deleted + ' 件削除、残り ' + d.remaining.toLocaleString() + ' 件');
        if (d.failed.length) log('削除失敗 ID：' + d.failed.join(', '), 'warn');

        if (d.remaining === 0) {
          log('投稿の削除完了しました', 'success');
          break;
        }
        if (d.deleted === 0) {
          log('削除できる投稿がないため停止しました', 'warn');
          break;
        }
        if (!running) break;
      }

      // タクソノミーのターム削除
      if (running && purgeTaxonomies.length > 0) {
        log('タクソノミーのターム削除を開始...');
        for (const taxonomy of purgeTaxonomies) {
          const tbody = new FormData();
          tbody.append('action', ACTION);
          tbody.append('_ajax_nonce', nonce);
          tbody.append('post_type', postType);
          tbody.append('purge_taxonomy', taxonomy);
          try {
            const r = await fetch(ajaxUrl, {
              method: 'POST',
              body: tbody,
              credentials: 'same-origin'
            });
            const res = await r.json();
            if (res.success) {
              log('「' + taxonomy + '」: ' + res.data.terms_deleted + ' ターム削除', 'success');
            } else {
              log('「' + taxonomy + '」削除エラー: ' + (res.data?.message ?? '不明'), 'error');
            }
          } catch (e) {
            log('「' + taxonomy + '」通信エラー: ' + e.message, 'error');
          }
        }
      }

      log('すべての処理が完了しました', 'success');
      running = false;
      $el('btn-start').disabled = false;
      $el('btn-stop').disabled = true;
    }

    $el('btn-start').addEventListener('click', () => {
      const taxList = getCheckedTaxonomies();
      const msg = taxList.length ?
        `「${postType}」を全件削除し、タクソノミー（${taxList.join(', ')}）のタームも削除します` :
        `「${postType}」を全件完全削除します`;
      if (confirm(msg)) run();
    });
    $el('btn-stop').addEventListener('click', () => {
      running = false;
      log('停止を受け付けました（処理中のリクエスト完了後に停止）', 'warn');
    });
  }());
</script>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  *,
  *::before,
  *::after {
    box-sizing: border-box;
  }

  #wpwrap {
    background: #fafafa;
  }

  #wpcontent {
    padding-right: 20px;
  }

  #wpfooter {
    display: none;
  }

  .notice {
    display: none !important;
  }

  .wrap {
    max-width: none;
    margin: 0;
    padding: 0;
  }

  .purge-tool {
    padding: 48px 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }

  /* ヘッダー */
  .purge-tool__header {
    margin-bottom: 32px;
  }

  .purge-back {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.8125rem;
    color: #71717a;
    text-decoration: none;
    margin-bottom: 12px;
    transition: color 0.15s;
  }

  .purge-back:hover {
    color: #18181b;
  }

  .purge-tool__header h1 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.5rem;
    font-weight: 700;
    color: #18181b;
    margin: 0;
    letter-spacing: -0.02em;
  }

  .purge-tool__type {
    color: #a1a1aa;
    font-weight: 400;
    font-size: 1rem;
  }

  .purge-tool__badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 10px;
    background: #fef2f2;
    color: #b91c1c;
    border-radius: 99px;
    border: 1px solid #fecaca;
  }

  /* グリッド */
  .purge-tool__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
  }

  @media (max-width: 900px) {
    .purge-tool__grid {
      grid-template-columns: 1fr;
    }
  }

  /* セクション */
  .purge-section {
    background: #fff;
    border: 1px solid #e4e4e7;
    border-radius: 12px;
    padding: 28px 32px;
  }

  .purge-section h2 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #18181b;
    margin: 0 0 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f4f4f5;
  }

  /* プログレス */
  .purge-progress {
    margin-bottom: 20px;
  }

  .purge-progress__bar {
    height: 8px;
    background: #f4f4f5;
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: 8px;
  }

  .purge-progress__fill {
    height: 100%;
    width: 0;
    background: #b91c1c;
    border-radius: 99px;
    transition: width 0.3s ease;
  }

  .purge-progress__meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #71717a;
  }

  .purge-progress__pct {
    font-weight: 600;
    color: #b91c1c;
  }

  /* ステータス */
  .purge-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 20px;
  }

  .purge-stat {
    background: #fafafa;
    border: 1px solid #f4f4f5;
    border-radius: 8px;
    padding: 12px 14px;
  }

  .purge-stat__label {
    display: block;
    font-size: 0.6875rem;
    font-weight: 500;
    color: #a1a1aa;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
  }

  .purge-stat__value {
    display: block;
    font-size: 1.125rem;
    font-weight: 700;
    color: #18181b;
    font-variant-numeric: tabular-nums;
  }

  .purge-stat__value.-deleted {
    color: #b91c1c;
  }

  /* ボタン */
  .purge-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
  }

  .purge-btn {
    padding: 10px 22px;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
  }

  .purge-btn.-danger {
    background: #b91c1c;
    color: #fff;
  }

  .purge-btn.-danger:hover {
    background: #991b1b;
  }

  .purge-btn.-danger:active {
    transform: scale(0.98);
  }

  .purge-btn.-danger:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  .purge-btn.-ghost {
    background: transparent;
    color: #52525b;
    border: 1px solid #e4e4e7;
  }

  .purge-btn.-ghost:hover {
    background: #f4f4f5;
  }

  .purge-btn.-ghost:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  .purge-note {
    font-size: 0.75rem;
    color: #a1a1aa;
    margin: 0 0 14px;
  }

  /* ログ */
  .purge-log {
    max-height: 220px;
    overflow-y: auto;
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    background: #fafafa;
    padding: 8px;
    font-size: 0.75rem;
    font-family: 'Menlo', 'Monaco', 'Consolas', monospace;
    display: flex;
    flex-direction: column;
    gap: 1px;
  }

  .purge-log__item {
    padding: 2px 6px;
    border-radius: 4px;
    color: #3f3f46;
  }

  .purge-log__item.-success {
    color: #15803d;
  }

  .purge-log__item.-warn {
    color: #b45309;
  }

  .purge-log__item.-error {
    color: #dc2626;
    background: #fef2f2;
  }

  /* タクソノミー */
  .purge-taxonomy-note {
    font-size: 0.8125rem;
    color: #71717a;
    margin: -8px 0 16px;
  }

  .purge-taxonomies {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .purge-taxonomy {
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    overflow: hidden;
    transition: border-color 0.15s;
  }

  .purge-taxonomy:has(input:checked) {
    border-color: #b91c1c;
  }

  .purge-taxonomy__head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    cursor: pointer;
    background: #fafafa;
    user-select: none;
  }

  .purge-taxonomy__head:hover {
    background: #f4f4f5;
  }

  .purge-taxonomy__name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #18181b;
  }

  .purge-taxonomy__slug {
    font-size: 0.75rem;
    color: #71717a;
    background: #f4f4f5;
    padding: 1px 7px;
    border-radius: 4px;
    font-family: 'Menlo', monospace;
  }

  .purge-taxonomy__count {
    font-size: 0.75rem;
    color: #a1a1aa;
    margin-left: auto;
  }

  /* カスタムチェックボックス */
  .purge-checkbox {
    position: relative;
    display: inline-block;
    width: 18px;
    height: 18px;
    flex-shrink: 0;
  }

  .purge-checkbox input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
  }

  .purge-checkbox__mark {
    position: absolute;
    inset: 0;
    border: 1.5px solid #d4d4d8;
    border-radius: 4px;
    background: #fff;
    transition: all 0.15s;
  }

  .purge-checkbox input:checked~.purge-checkbox__mark {
    background: #b91c1c;
    border-color: #b91c1c;
  }

  .purge-checkbox__mark::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 9px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    opacity: 0;
    transition: opacity 0.1s;
  }

  .purge-checkbox input:checked~.purge-checkbox__mark::after {
    opacity: 1;
  }

  /* タームチップ */
  .purge-terms {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 10px 14px;
    border-top: 1px solid #f4f4f5;
  }

  .purge-term {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    background: #f4f4f5;
    border-radius: 99px;
    font-size: 0.75rem;
    color: #3f3f46;
  }

  .purge-term__count {
    background: #e4e4e7;
    border-radius: 99px;
    padding: 0px 5px;
    font-size: 0.6875rem;
    color: #71717a;
    font-variant-numeric: tabular-nums;
  }

  .purge-no-terms {
    padding: 10px 14px;
    font-size: 0.8125rem;
    color: #a1a1aa;
    font-style: italic;
    border-top: 1px solid #f4f4f5;
    margin: 0;
  }

  /* タクソノミーなし */
  .purge-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 40px 0;
    color: #a1a1aa;
    font-size: 0.875rem;
  }

  .purge-empty p {
    margin: 0;
  }
</style>
