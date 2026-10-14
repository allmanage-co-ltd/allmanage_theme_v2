# cookie-consent — GDPR 対応 Cookie 同意管理モジュール

PHP 側で同意状態を判定し、**許可されたタグだけ HTML に出力する**方式の Cookie 同意管理モジュール。

「GA4 をロードしてから止める」ではなく「許可されるまで GA4 が存在しない」設計のため、
意図しない通信が発生しない。

---

## 有効化

`modules/index.php` の該当行のコメントを外す：

```php
require_once __DIR__ . '/cookie-consent/cookie-consent.php';
```

---

## カテゴリ

| カテゴリ | 説明 | デフォルト |
|---|---|---|
| `necessary` | 必須。ログインセッション・フォーム等 | 常に有効（拒否不可） |
| `functional` | 機能。YouTube / Google Maps iframe 等 | 拒否 |
| `analytics` | 分析。GA4 / Microsoft Clarity 等 | 拒否 |
| `marketing` | 広告。Meta Pixel / Google Ads 等 | 拒否 |

---

## フィルターフックによるカスタマイズ

```php
// GA4 測定 ID（必須）
add_filter('cookie_consent/ga4_id', fn() => 'G-XXXXXXXXXX');

// Microsoft Clarity（不要なら設定不要）
add_filter('cookie_consent/clarity_id', fn() => 'xxxxxxxxxx');

// バナーに表示するサイト名
add_filter('cookie_consent/site_name', fn() => 'My Company');

// プライバシーポリシーの URL
add_filter('cookie_consent/privacy_url', fn() => '/privacy-policy/');

// functional カテゴリで制御する追加ホスト
add_filter('cookie_consent/functional_hosts', function ($hosts) {
    $hosts[] = 'maps.googleapis.com';
    return $hosts;
});
```

---

## GA4 以外の analytics タグを追加する

`cookie_consent/ga4_id` フィルターで対応していないタグは `wp_head` フックに priority を指定して追加する：

```php
add_action('wp_head', function () {
    if (!\CookieConsent\has_cookie_consent('analytics')) {
        return;
    }
    // Meta Pixel 等を出力
}, 2);
```

---

## marketing タグを追加する

```php
add_action('wp_head', function () {
    if (!\CookieConsent\has_cookie_consent('marketing')) {
        return;
    }
    // Google Ads タグ等を出力
}, 3);
```

---

## iframe の自動ブロック

`functional` カテゴリに同意していない場合、下記ホストを含む `<iframe>` の `src` を自動でブロックし、
プレースホルダーに差し替える。

**デフォルト対象ホスト:**
- `youtube.com`
- `youtube-nocookie.com`
- `google.com/maps`
- `maps.googleapis.com`

追加は `cookie_consent/functional_hosts` フィルターで。

---

## 同意状態の確認方法

```php
// テンプレート内での使用例
if (\CookieConsent\has_cookie_consent('functional')) {
    // YouTube iframe を出力
} else {
    // プレースホルダーを出力（JS が自動で処理するが、PHP 側でも制御可能）
}
```

---

## 動作の仕組み

```
初回アクセス
  └─ Cookie なし
       ├─ GA4 / 広告タグ → HTML に出力しない
       └─ バナー表示

ユーザーが選択・保存
  └─ Cookie に { necessary, functional, analytics, marketing } を保存
       └─ ページリロード

リロード後
  └─ PHP が Cookie を読む
       ├─ analytics: true → GA4 タグを出力
       ├─ functional: false → iframe をブロック
       └─ ...
```

リロード方式のため「動的ロード」の複雑な処理を避け、事故を防いでいる。

---

## 検証方法

Chrome DevTools で以下を確認する：

1. **Application → Cookies** — `cookie_consent` の内容を確認
2. **Network → Filter: googletagmanager.com** — GA4 通信の有無を確認
3. **初回訪問（Cookie 削除後）** — GA4 リクエストが発生しないことを確認

---

## ファイル構成

```
cookie-consent/
├── cookie-consent.php   # エントリポイント（フック登録・タグ出力制御）
├── class-consent.php    # 同意状態管理クラス
├── assets/
│   ├── cookie-consent.js  # UI 制御・Cookie 保存
│   └── cookie-consent.css # スタイル
└── README.md
```
