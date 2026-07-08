<?php

use App\Services\Csv\ExportCsvAbstract;
use App\Services\Csv\ImportCsvAbstract;
use App\Services\Config;

$exporters = Config::get('cms.option_pages.csv-in-expoter.exporter');
$importers  = Config::get('cms.option_pages.csv-in-expoter.importer');
?>

<div class="wrap">
  <div class="csv-tool">

    <h1>CSVインポート / エクスポート</h1>

    <?php if (isset($_GET[ImportCsvAbstract::successParam()])): ?>
      <div class="csv-success">インポート完了</div>

      <script>
        if (window.history.replaceState) {
          const url = new URL(window.location);
          url.searchParams.delete('<?= ImportCsvAbstract::successParam() ?>');
          window.history.replaceState({}, document.title, url.pathname + url.search);
        }
      </script>
    <?php endif; ?>

    <div class="csv-tool__grid">

      <section class="csv-section -import">
        <h2>インポート</h2>

        <?php if (!empty($importers)): ?>
          <form method="post" enctype="multipart/form-data" class="csv-form" id="csv-import-form">

            <div class="csv-field">
              <label>投稿タイプ</label>
              <select name="<?= ImportCsvAbstract::importParam() ?>">
                <?php foreach ($importers as $class): ?>
                  <option value="<?= $class::postType() ?>">
                    <?= $class::postType() ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="csv-field">
              <label>CSVファイル</label>
              <input type="file" name="csv" accept=".csv" required>
            </div>

            <div class="csv-field">
              <label>
                <input type="checkbox" name="<?= ImportCsvAbstract::dryRunParam() ?>" value="1">
                dry run（実行結果ログのみ表示）
              </label>
            </div>

            <button type="submit" class="csv-button -import" id="csv-import-btn">
              CSVをアップロード
            </button>
          </form>

          <div class="csv-progress" id="csv-progress" hidden>
            <div class="csv-progress__header">
              <span class="csv-progress__label" id="csv-progress-label"></span>
              <span class="csv-progress__count" id="csv-progress-count">0 / 0</span>
            </div>
            <div class="csv-progress__bar">
              <div class="csv-progress__fill" id="csv-progress-fill"></div>
            </div>
            <div class="csv-progress__log" id="csv-progress-log"></div>
          </div>
        <?php endif; ?>
      </section>

      <section class="csv-section -export">
        <h2>エクスポート</h2>

        <?php if (!empty($exporters)): ?>
          <form method="get" class="csv-form" id="csv-export-form">

            <div class="csv-field">
              <label>投稿タイプ</label>
              <select name="<?= ExportCsvAbstract::exportParam() ?>" id="csv-export-post-type">
                <?php foreach ($exporters as $class): ?>
                  <option value="<?= esc_attr($class::postType()) ?>">
                    <?= esc_html($class::postType()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="csv-field">
              <label>ファイル名（.csv）</label>
              <input type="text" name="<?= ExportCsvAbstract::filenameParam() ?>" id="csv-export-filename" value="<?= esc_attr($exporters[array_key_first($exporters)]::postType()) ?>_<?= date('YmdHis') ?>">
            </div>

            <div class="csv-field">
              <label>
                <input type="checkbox" name="<?= ExportCsvAbstract::dryRunParam() ?>" value="1" id="csv-export-dryrun">
                dry run（実行結果ログのみ表示）
              </label>
            </div>

            <button type="submit" class="csv-button -export" id="csv-export-btn">
              CSVをダウンロード
            </button>
          </form>

          <div class="csv-progress" id="csv-export-progress" hidden>
            <div class="csv-progress__header">
              <span class="csv-progress__label" id="csv-export-progress-label"></span>
            </div>
            <div class="csv-progress__log" id="csv-export-progress-log"></div>
          </div>
        <?php endif; ?>
      </section>

    </div>
  </div>
</div>

<script>
  // エクスポートフォーム：ファイル名デフォルト表示更新 & dry run 結果表示
  (function() {
    const exportForm = document.getElementById('csv-export-form');
    if (!exportForm) return;

    const postTypeSelect = document.getElementById('csv-export-post-type');
    const filenameInput = document.getElementById('csv-export-filename');
    const dryRunCheckbox = document.getElementById('csv-export-dryrun');
    const exportBtn = document.getElementById('csv-export-btn');
    const exportProgress = document.getElementById('csv-export-progress');
    const exportProgressLabel = document.getElementById('csv-export-progress-label');
    const exportProgressLog = document.getElementById('csv-export-progress-log');

    function updateFilenameDefault() {
      if (!filenameInput || !postTypeSelect) return;
      const now = new Date();
      const pad = (n, d = 2) => String(n).padStart(d, '0');
      const stamp = String(now.getFullYear()) +
        pad(now.getMonth() + 1) +
        pad(now.getDate()) +
        pad(now.getHours()) +
        pad(now.getMinutes()) +
        pad(now.getSeconds());
      filenameInput.value = postTypeSelect.value + '_' + stamp;
    }

    postTypeSelect && postTypeSelect.addEventListener('change', updateFilenameDefault);

    // dry run のとき GET フォームを fetch で受け取ってテーブルHTMLを枠内に表示
    exportForm.addEventListener('submit', function(e) {
      if (!dryRunCheckbox || !dryRunCheckbox.checked) return;

      e.preventDefault();

      exportProgress.hidden = false;
      exportProgressLabel.textContent = 'dry run 実行中...';
      exportProgressLog.innerHTML = '';
      exportBtn.disabled = true;
      exportBtn.textContent = '処理中...';

      const params = new URLSearchParams(new FormData(exportForm));
      fetch(location.pathname + '?' + params.toString(), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(r => {
          if (!r.ok) throw new Error('サーバーエラー: ' + r.status);
          return r.text();
        })
        .then(html => {
          exportProgressLabel.textContent = 'dry run 完了';
          exportProgressLog.innerHTML = '<div class="csv-dryrun-table">' + html + '</div>';
        })
        .catch(err => {
          exportProgressLabel.textContent = 'エラーが発生しました';
          exportProgressLog.innerHTML = '<div class="csv-log-item -error">' + err.message + '</div>';
        })
        .finally(() => {
          exportBtn.disabled = false;
          exportBtn.textContent = 'CSVをダウンロード';
        });
    });
  })();

  // インポートフォーム
  (function() {
    const form = document.getElementById('csv-import-form');
    const btn = document.getElementById('csv-import-btn');
    const progress = document.getElementById('csv-progress');
    const label = document.getElementById('csv-progress-label');
    const count = document.getElementById('csv-progress-count');
    const fill = document.getElementById('csv-progress-fill');
    const log = document.getElementById('csv-progress-log');

    if (!form) return;

    form.addEventListener('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(form);
      const isDryRun = formData.has('<?= ImportCsvAbstract::dryRunParam() ?>');

      // UI 初期化
      progress.hidden = false;
      log.innerHTML = '';
      fill.style.width = '0%';
      label.textContent = isDryRun ? 'dry run 実行中...' : 'インポート中...';
      count.textContent = '0 / ?';
      btn.disabled = true;
      btn.textContent = '処理中...';

      try {
        const resp = await fetch(form.action || location.href, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData,
        });

        if (!resp.ok) throw new Error('サーバーエラー: ' + resp.status);

        const reader = resp.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
          const {
            done,
            value
          } = await reader.read();
          if (done) break;

          buffer += decoder.decode(value, {
            stream: true
          });
          const lines = buffer.split('\n');
          buffer = lines.pop(); // 未完結行はバッファに残す

          for (const line of lines) {
            if (!line.trim()) continue;
            let data;
            try {
              data = JSON.parse(line);
            } catch {
              continue;
            }

            if (data.error) {
              appendLog('error', 'エラー: ' + data.error);
              continue;
            }

            if (data.done && data.dryRun) {
              // dry run 完了：ログ一覧を表示
              label.textContent = 'dry run 完了';
              fill.style.width = '100%';
              count.textContent = data.log.length + ' 行';
              log.innerHTML = '<pre style="margin:0;font-size:0.75rem;overflow:auto;">' +
                JSON.stringify(data.log, null, 2) + '</pre>';
              continue;
            }

            if (data.done) {
              label.textContent = 'インポート完了！';
              fill.style.width = '100%';
              setTimeout(() => {
                location.href = data.redirectUrl;
              }, 800);
              continue;
            }

            // 通常進捗
            const pct = data.total > 0 ? Math.round(data.processed / data.total * 100) : 0;
            fill.style.width = pct + '%';
            count.textContent = data.processed + ' / ' + data.total;
            appendLog('ok', '[' + data.processed + '/' + data.total + '] ' + (data.title || '(無題)') + ' (post_id: ' + data.post_id + ')');
          }
        }
      } catch (err) {
        appendLog('error', 'エラー: ' + err.message);
        label.textContent = 'エラーが発生しました';
      } finally {
        btn.disabled = false;
        btn.textContent = 'CSVをアップロード';
      }
    });

    function appendLog(type, text) {
      const el = document.createElement('div');
      el.className = 'csv-log-item -' + type;
      el.textContent = text;
      log.appendChild(el);
      log.scrollTop = log.scrollHeight;
    }
  })();
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

  .csv-tool {
    padding: 48px 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    max-width: 100%;
    margin: 0 auto;
  }

  .csv-tool h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 32px;
    color: #18181b;
    letter-spacing: -0.025em;
  }

  .csv-success {
    margin-bottom: 24px;
    padding: 14px 18px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    color: #15803d;
    font-size: 0.875rem;
    font-weight: 500;
  }

  .csv-tool__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }

  @media (max-width: 900px) {
    .csv-tool__grid {
      grid-template-columns: 1fr;
    }
  }

  .csv-section {
    background: #fff;
    border: 1px solid #e4e4e7;
    border-radius: 12px;
    padding: 32px;
    min-height: 320px;
    overflow-y: hidden;
  }

  .csv-section h2 {
    font-size: 1rem;
    font-weight: 600;
    color: #18181b;
    margin: 0 0 20px 0;
    padding-bottom: 16px;
    border-bottom: 1px solid #f4f4f5;
  }

  .csv-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .csv-field {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .csv-field>label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #3f3f46;
  }

  .csv-field select {
    appearance: none;
    background-color: #fff;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 14px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding: 12px 44px 12px 16px;
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    font-size: 0.9375rem;
    color: #18181b;
    cursor: pointer;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    width: 100%;
    min-width: 100%;
  }

  .csv-field select:hover {
    border-color: #a1a1aa;
  }

  .csv-field select:focus {
    outline: none;
    border-color: #18181b;
    box-shadow: 0 0 0 3px rgba(24, 24, 27, 0.08);
  }

  .csv-field input[type="file"] {
    font-size: 0.9375rem;
    color: #52525b;
    padding: 14px 16px;
    border: 1px dashed #d4d4d8;
    border-radius: 8px;
    background: #fafafa;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
  }

  .csv-field input[type="file"]:hover {
    border-color: #a1a1aa;
    background: #f4f4f5;
  }

  .csv-field input[type="file"]::file-selector-button {
    display: none;
  }

  .csv-field label:has(input[type="checkbox"]) {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 0.8125rem;
    font-weight: 400;
    color: #52525b;
    cursor: pointer;
  }

  .csv-field input[type="checkbox"] {
    appearance: none;
    width: 18px;
    height: 18px;
    border: 1.5px solid #d4d4d8;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    transition: all 0.15s ease;
    position: relative;
  }

  .csv-field input[type="checkbox"]:hover {
    border-color: #a1a1aa;
  }

  .csv-field input[type="checkbox"]:checked {
    background: #18181b;
    border-color: #18181b;
  }

  .csv-field input[type="checkbox"]:checked::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 9px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
  }

  .csv-field input[type="checkbox"]:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(24, 24, 27, 0.08);
  }

  .csv-button {
    margin-top: 8px;
    padding: 14px 24px;
    border-radius: 8px;
    border: none;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .csv-button.-export {
    background: #18181b;
    color: #fff;
  }

  .csv-button.-export:hover {
    background: #27272a;
  }

  .csv-button.-export:active {
    transform: scale(0.98);
  }

  .csv-button.-import {
    background: #18181b;
    color: #fff;
  }

  .csv-button.-import:hover {
    background: #27272a;
  }

  .csv-button.-import:active {
    transform: scale(0.98);
  }

  .csv-button:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(24, 24, 27, 0.2);
  }

  .csv-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .csv-progress {
    margin-top: 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .csv-progress[hidden] {
    display: none;
  }

  .csv-progress__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8125rem;
    color: #52525b;
  }

  .csv-progress__label {
    font-weight: 500;
    color: #18181b;
  }

  .csv-progress__count {
    font-variant-numeric: tabular-nums;
    color: #71717a;
  }

  .csv-progress__bar {
    height: 6px;
    background: #e4e4e7;
    border-radius: 99px;
    overflow: hidden;
  }

  .csv-progress__fill {
    height: 100%;
    background: #18181b;
    border-radius: 99px;
    width: 0%;
    transition: width 0.3s ease;
  }

  .csv-progress__log {
    max-height: 240px;
    overflow: auto;
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    background: #fafafa;
    padding: 8px;
    display: block;
    font-size: 0.75rem;
    font-family: 'Menlo', 'Monaco', 'Consolas', monospace;
  }

  .csv-log-item {
    padding: 2px 6px;
    border-radius: 4px;
    color: #3f3f46;
  }

  .csv-log-item.-ok {
    color: #15803d;
  }

  .csv-log-item.-error {
    color: #dc2626;
    background: #fef2f2;
  }

  .csv-field input[type="text"] {
    appearance: none;
    background-color: #fff;
    padding: 14px 16px;
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-family: inherit;
    color: #18181b;
    width: 100%;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
  }

  .csv-field input[type="text"]::placeholder {
    color: #a1a1aa;
  }

  .csv-field input[type="text"]:hover {
    border-color: #a1a1aa;
  }

  .csv-field input[type="text"]:focus {
    outline: none;
    border-color: #18181b;
    box-shadow: 0 0 0 3px rgba(24, 24, 27, 0.08);
  }

  .csv-dryrun-table {
    font-size: 0.75rem;
    font-family: 'Menlo', 'Monaco', 'Consolas', monospace;
  }

  .csv-dryrun-table table {
    border-collapse: collapse;
    white-space: nowrap;
  }

  .csv-dryrun-table th,
  .csv-dryrun-table td {
    padding: 4px 8px;
    border: 1px solid #e4e4e7;
  }

  .csv-dryrun-table th {
    background: #f4f4f5;
    font-weight: 600;
  }
</style>
