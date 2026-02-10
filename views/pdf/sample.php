<?php
/**
 * @var string $key
 */
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>御見積書</title>

<style>
/* ===== Theme ===== */
:root {
  --border: #333;
  --border-light: #bbb;
  --bg-header: #f2f2f2;
  --bg-accent: #fafafa;
  --text-main: #000;
  --text-sub: #555;
  --font-base: kozminproregular;
}

/* ===== Base ===== */
body {
  font-family: var(--font-base);
  font-size: 10pt;
  color: var(--text-main);
  line-height: 1.6;
}

h1 {
  font-size: 16pt;
}

/* ===== Layout ===== */
.block {
  margin-top: 14px;
}

/* ===== Table Common ===== */
.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 7px 9px;
  border: 1px solid var(--border);
}

.table th {
  background: var(--bg-header);
  font-weight: bold;
}

/* ===== Info ===== */
.table-info th {
  width: 26%;
}
.table-info td {
  width: 74%;
}

/* ===== Detail ===== */
.table-detail th {
  text-align: center;
}
.table-detail td {
  vertical-align: middle;
}

.text-center {
  text-align: center;
}
.text-right {
  text-align: right;
}

/* ===== Total ===== */
.total-box {
  margin-top: 12px;
}

.total-box td {
  border: 1px solid var(--border);
  background: var(--bg-accent);
  font-size: 11.5pt;
  font-weight: bold;
  padding: 12px;
  text-align: right;
}

/* ===== Footer ===== */
.footer {
  margin-top: 24px;
  padding-top: 8px;
  border-top: 1px solid var(--border-light);
  font-size: 9pt;
  color: var(--text-sub);
}
</style>
</head>

<body>

<h1>御見積書</h1>

<div class="block">
  <table class="table table-info">
    <tr>
      <th>発行日</th>
      <td>2026年2月10日</td>
    </tr>
    <tr>
      <th>お客様名</th>
      <td>テスト株式会社 様</td>
    </tr>
    <tr>
      <th>件名</th>
      <td>サンプルPDF作成</td>
    </tr>
  </table>
</div>

<div class="block">
  <table class="table table-detail">
    <tr>
      <th style="width:60%">商品名</th>
      <th style="width:20%">数量</th>
      <th style="width:20%">金額</th>
    </tr>
    <tr>
      <td><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></td>
      <td class="text-center">1</td>
      <td class="text-right">1,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
    <tr>
      <td>テスト商品B</td>
      <td class="text-center">2</td>
      <td class="text-right">2,000円</td>
    </tr>
  </table>
</div>

<table class="table total-box">
  <tr>
    <td>合計金額　3,000円（税込）</td>
  </tr>
</table>

<div class="footer">
  ※本書はサンプルとして自動生成されています<br>
  ※内容に関するお問い合わせは担当者までご連絡ください
</div>

</body>
</html>
