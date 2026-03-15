# VIEWSの記述について

1. viewsで呼び出せる独自グローバル関数は全て`bootstrap/functions.php`で定義しています。

    まずはこちらを一読し、呼べる関数をなんとなくざっくりと把握してください。

2. 保守性の観点から、見た目（HTML）とロジックは分離する。

    PHPでロジックを書く必要がある場合は、以下の方針で実装する。

- **ロジックが単純で短い場合**
  - `bootstrap/functions.php` に直接関数を定義する
  - もしくは、テンプレートのphpブロックに関数を定義する
  - ※基本的にはどちらも非推奨で、単純なロジックでも`app/`の適切なクラスに逃がすべきです。

- **ロジックが少しでも複雑になる場合**
  1. `app/UseCase/` などの適切なクラスにロジックを実装する
  2. `bootstrap/functions.php` に中間関数を定義し、viewsから呼び出す

*ソースコードはいつだれが触るかわからないので、次に対応するエンジニアをぐちゃぐちゃなソースコードで苦しめないために、誰が見てもすっとわかるようにコメントや意図を明記することを意識してコードを書いてください。*

## 保守性を高めるコーディングルール

- PHPの処理はできるだけファイル上部にまとめる

- 同じ処理は関数に切り出し、重複して書かないようにする

- 関数定義と変数定義は分けて整理する

- 変数名は役割が分かる名前にし、意図が伝わりづらい場合はコメントを残す

- 関数や複雑な処理には必ずコメントを書き、意図が一目でわかるようにする

- 冗長な書き方はPHP8系をベースに修正し可読性を保つ

- 変数の上書きは避ける（例: $img / $index などの再利用）

- HTML内にPHPを書く場合は最小限にとどめる

## 悪い例（ロジックがHTMLに混ざりスパゲッティ化している）

*HTMLの中にロジックが散在すると、修正時に処理を追いづらくなり、わざわざコードを読んで意図を汲み取らせる事を強制させてしまう*

- get_posts() がHTMLの中

- 変数生成がHTMLの途中

- 条件分岐がDOMの途中

- タイトル加工がDOMの途中

- 可読性が低く修正しづらい

```html
<main>
  <ul>
    <?php
    foreach (get_posts(['post_type' => 'news', 'posts_per_page' => 3]) as $post) {
    ?>
      <li>
        <a href="<?= get_permalink($post->ID) ?>">
          <?php
          if (strtotime(get_the_date('Y-m-d', $post->ID)) > strtotime('-7 days')) {
          ?>
            <span class="badge">NEW</span>
          <?php
          }
          ?>
          <img src="<?php
            $img = get_the_post_thumbnail_url($post->ID, 'medium');

            if (!$img) {
              $img = '/assets/img/noimage.jpg';
            }

            echo $img;
          ?>">
          <h3>
            <?php
            $title = get_the_title($post->ID);

            if (mb_strlen($title) > 20) {
              echo mb_substr($title, 0, 20) . '...';
            } else {
              echo $title;
            }
            ?>
          </h3>
          <p><?= get_the_date('Y.m.d', $post->ID) ?></p>
        </a>
      </li>
    <?php
    }
    ?>
  </ul>
</main>
```

## 改善例

- PHP処理をファイル上部にまとめる

- HTMLは描画と最小限のPHPにとどめる

- 表示用データを配列に整形する

```html
<?php

$posts = get_posts([
  'post_type'      => 'news',
  'posts_per_page' => 3
]);

$post_items = [];

foreach ($posts as $post) {
  $title = get_the_title($post->ID);
  if (mb_strlen($title) > 20) {
    $title = mb_substr($title, 0, 20) . '...';
  }

  $img = get_the_post_thumbnail_url($post->ID, 'medium');
  if (!$img) {
    $img = '/assets/img/noimage.jpg';
  }

  $post_items[] = [
    'title'  => $title,
    'url'    => get_permalink($post->ID),
    'date'   => get_the_date('Y.m.d', $post->ID),
    'img'    => $img,
    'is_new' => strtotime(get_the_date('Y-m-d', $post->ID)) > strtotime('-7 days')
  ];
}
?>

<main>
  <ul>
    <?php foreach ($post_items as $item): ?>
      <li>
        <a href="<?= $item['url'] ?>">
          <?php if ($item['is_new']): ?>
            <span class="badge">NEW</span>
          <?php endif; ?>
          <img src="<?= $item['img'] ?>">
          <h3><?= $item['title'] ?></h3>
          <p><?= $item['date'] ?></p>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</main>
```

## さらに改善した例（テーマ推奨）

1. ロジックを `app` ディレクトリのクラスに分離し、再利用性と保守性を高める

2. `bootstrap/functions.php` は中間関数のみを定義し、肥大化を防ぐ
    - bootstrap/functions.php自体が関数ドキュメントにもなるのでひと手間以上の価値がある

3. `views` は描画専用とし、ロジックを含めない

4. 適切にコメントを記述し、他のエンジニアの理解コストを減らす

viewsにロジックを書くとテンプレートがスパゲッティ化し、
修正コストが急激に増えるため。

```php
# app/UseCase/GetNewsCards.php

/**
 * ニュースカード一覧取得UseCase
 *
 * news投稿タイプから指定件数の記事を取得し、
 * viewsで描画しやすい形式の配列へ整形して返す
 */
class GetNewsCards
{
  /**
   * ニュースカードデータ取得
   *
   * - news投稿を取得
   * - タイトル文字数を調整
   * - サムネイル取得（未設定時はデフォルト画像）
   * - 公開日取得
   * - 新着判定（7日以内）
   */
  public function execute(): array
  {
    $posts = get_posts([
      'post_type'      => 'news',
      'posts_per_page' => 3
    ]);

    $items = [];

    foreach ($posts as $post) {
      $title = get_the_title($post->ID);

      // タイトル文字数制限
      if (mb_strlen($title) > 20) {
        $title = mb_substr($title, 0, 20) . '...';
      }

      $img = get_the_post_thumbnail_url($post->ID, 'medium');
      if (!$img) {
        $img = '/assets/img/noimage.jpg';
      }

      $items[] = [
        'title'  => $title,
        'url'    => get_permalink($post->ID),
        'date'   => get_the_date('Y.m.d', $post->ID),
        'img'    => $img,
        // 公開日が7日以内なら新着扱い
        'is_new' => strtotime(get_the_date('Y-m-d', $post->ID)) > strtotime('-7 days')
      ];
    }

    // 整形済みデータ返却
    return $items;
  }
}
```

```php
# bootstrap/functions.php

/**
 * news投稿タイプの一覧を取得する
 *
 * 使用例:
 *  $news_cards = get_news_cards();
 */
function get_news_cards(): array
{
  return (new \App\UseCase\GetNewsCards())->execute();
}
```

```html
<?php

// お知らせ投稿タイプの一覧データ配列
$news_cards = get_news_cards();
?>

<main>
  <ul>
    <?php foreach ($news_cards as $card): ?>
      <li>
        <a href="<?= $card['url'] ?>">
          <?php if ($card['is_new']): ?>
            <span class="badge">NEW</span>
          <?php endif; ?>
          <img src="<?= $card['img'] ?>">
          <h3><?= $card['title'] ?></h3>
          <p><?= $card['date'] ?></p>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</main>
```
