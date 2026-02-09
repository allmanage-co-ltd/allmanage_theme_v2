<div class="wrap">
  <div class="csv-tool">

    <h1>【未実装】CSVインポート / エクスポート</h1>

    <!-- Export -->
    <section class="csv-section -export">
      <h2>エクスポート</h2>

      <form method="post" action="<?= admin_url('admin-post.php') ?>" class="csv-form">
        <input type="hidden" name="action" value="csv_export">

        <div class="csv-field">
          <label>投稿タイプ</label>
          <input type="text" name="post_type" value="news">
        </div>

        <button type="submit" class="csv-button -export">
          CSVをダウンロード
        </button>
      </form>
    </section>

    <!-- Import -->
    <section class="csv-section -import">
      <h2>インポート</h2>

      <form method="post" action="<?= admin_url('admin-post.php') ?>" enctype="multipart/form-data" class="csv-form">
        <input type="hidden" name="action" value="csv_import">

        <div class="csv-field">
          <label>投稿タイプ</label>
          <input type="text" name="post_type" value="news">
        </div>

        <div class="csv-field">
          <label>CSVファイル</label>
          <input type="file" name="csv" accept=".csv">
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
  max-width: 960px;
  margin: 0 auto;
  padding: 24px 0;
}

.csv-tool h1 {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 24px;
}

.csv-section {
  position: relative;
  padding-left: 16px;
  margin-bottom: 48px;
}

.csv-section::before {
  content: "";
  position: absolute;
  left: 0;
  top: 4px;
  width: 4px;
  height: calc(100% - 4px);
  border-radius: 2px;
}

.csv-section.-export::before {
  background: #2271b1;
  /* WP blue */
}

.csv-section.-import::before {
  background: #00a32a;
  /* WP green */
}

.csv-section h2 {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 16px;
}

.csv-form {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: flex-end;
}

.csv-field {
  flex: 1;
  min-width: 200px;
}

.csv-field label {
  display: block;
  font-size: 11px;
  color: #646970;
  margin-bottom: 4px;
}

.csv-field input[type="text"],
.csv-field input[type="file"] {
  width: 100%;
  border: none;
  border-bottom: 1px solid #c3c4c7;
  background: transparent;
  padding: 6px 2px;
  font-size: 14px;
}

.csv-field input:focus {
  outline: none;
  border-bottom-color: #2271b1;
}

.csv-section.-import .csv-field input:focus {
  border-bottom-color: #00a32a;
}

.csv-button {
  height: 36px;
  padding: 0 16px;
  border: none;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
}

.csv-button.-export {
  background: #2271b1;
}

.csv-button.-export:hover {
  background: #135e96;
}

.csv-button.-import {
  background: #00a32a;
}

.csv-button.-import:hover {
  background: #008a20;
}
</style>