<?php

use App\Support\Config;
?>

<div class="wrap">
    <div class="csv-tool">

        <h1>CSVインポート / エクスポート</h1>

        <?php if (isset($_GET['import_done'])): ?>
            <div class="csv-success">インポート完了</div>

            <script>
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('import_done');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                }
            </script>
        <?php endif; ?>


        <!-- Export -->
        <section class="csv-section -export">
            <h2>エクスポート</h2>

            <form method="get" class="csv-form">

                <div class="csv-field">
                    <label>投稿タイプ</label>
                    <select name="csv_export">
                        <?php
                        foreach (Config::get('csv.exporter') as $class):
                            $instance = new $class();
                        ?>
                            <option value="<?= $instance->key() ?>">
                                <?= $instance->key() ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="csv-button -export">
                    CSVをダウンロード
                </button>
            </form>
        </section>

        <!-- Import -->
        <section class="csv-section -import">
            <h2>インポート</h2>

            <form method="post" enctype="multipart/form-data" class="csv-form">

                <div class="csv-field">
                    <label>投稿タイプ</label>
                    <select name="csv_import">
                        <?php
                        foreach (Config::get('csv.importer') as $class):
                            $instance = new $class();
                        ?>
                            <option value="<?= $instance->key() ?>">
                                <?= $instance->key() ?>
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
                        <input type="checkbox" name="dry_run" value="1">
                        dry run（実行結果ログのみ表示）
                    </label>
                </div>

                <button type="submit" class="csv-button -import">
                    CSVをアップロード
                </button>
            </form>
        </section>
    </div>
</div>

<style>
    .csv-tool {
        max-width: 720px;
        margin: 40px auto;
        padding: 32px;
        background: #fff;
    }

    /* タイトル */
    .csv-tool h1 {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 32px;
    }

    /* セクション */
    .csv-section {
        padding: 24px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .csv-section:last-child {
        border-bottom: none;
    }

    /* 見出し */
    .csv-section h2 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 16px;
        color: #374151;
    }

    /* フォーム */
    .csv-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* フィールド */
    .csv-field {
        display: flex;
        flex-direction: column;
    }

    /* ラベル */
    .csv-field label {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    /* input */
    .csv-field input[type="text"],
    .csv-field input[type="file"] {
        font-size: 14px;
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff;
    }

    /* focus */
    .csv-field input:focus {
        outline: none;
        border-color: #111827;
    }

    .csv-field select {
        font-size: 14px;
        width: 100%;
        min-width: 100%;
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff;
        appearance: none;
        cursor: pointer;

        /* 矢印カスタム */
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 20 20' fill='none' stroke='%236b7280' stroke-width='1.5' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7l5 5 5-5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 16px;
    }

    /* focus */
    .csv-field select:focus {
        outline: none;
        border-color: #111827;
    }

    /* checkbox */
    .csv-field input[type="checkbox"] {
        margin-right: 6px;
    }

    /* ボタン */
    .csv-button {
        margin-top: 8px;
        height: 42px;
        padding: 0 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid transparent;
        transition: 0.15s;
    }

    /* export（アウトライン） */
    .csv-button.-export {
        background: transparent;
        border-color: #2563eb;
        color: #2563eb;
    }

    .csv-button.-export:hover {
        background: #2563eb;
        color: #fff;
    }

    /* import（塗り） */
    .csv-button.-import {
        background: #16a34a;
        color: #fff;
    }

    .csv-button.-import:hover {
        background: #15803d;
    }

    /* 完了メッセージ */
    .csv-success {
        margin-bottom: 16px;
        padding: 10px 14px;
        border-radius: 6px;
        background: #ecfdf5;
        color: #065f46;
        font-size: 13px;
    }
</style>
