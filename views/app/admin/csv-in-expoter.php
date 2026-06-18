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
          <form method="post" enctype="multipart/form-data" class="csv-form">

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

            <button type="submit" class="csv-button -import">
              CSVをアップロード
            </button>
          </form>
        <?php endif; ?>
      </section>

      <section class="csv-section -export">
        <h2>エクスポート</h2>

        <?php if (!empty($exporters)): ?>
          <form method="get" class="csv-form">

            <div class="csv-field">
              <label>投稿タイプ</label>
              <select name="<?= ExportCsvAbstract::exportParam() ?>">
                <?php foreach ($exporters as $class): ?>
                  <option value="<?= $class::postType() ?>">
                    <?= $class::postType() ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="csv-field">
              <label>
                <input type="checkbox" name="<?= ExportCsvAbstract::dryRunParam() ?>" value="1">
                dry run（実行結果ログのみ表示）
              </label>
            </div>

            <button type="submit" class="csv-button -export">
              CSVをダウンロード
            </button>
          </form>
        <?php endif; ?>
      </section>

    </div>
  </div>
</div>

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
</style>
