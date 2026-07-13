<?php

/** @var WP_Query | array $args */
$news_query = $args['news_query'];
?>

<?php if ($news_query->have_posts()): ?>
  <ul class="c-card_news">
    <?php while ($news_query->have_posts()): ?>
      <?php $news_query->the_post(); ?>
      <?php
      /**
       * ループ内変数
       */
      $cat = get_post_term('news_cat');
      $cat_name = $cat?->name ?? 'お知らせ';
      ?>
      <li class="c-card_news__item">
        <a href="<?php the_permalink(); ?>" class="c-card_news__link">
          <div class="c-card_news__info">
            <time datetime="<?php the_time('Y-m-d H:i:s'); ?>" class="c-card_news__date">
              <?php the_time('Y/m/d'); ?>
            </time>
            <div class="c-card_news__term">
              <?= $cat_name ?>
            </div>
          </div>
          <div class="c-card_news__ttl">
            <?php the_title(); ?>
          </div>
        </a>
      </li>
    <?php endwhile; ?>
  </ul>
  <?php
  the_pagination(
    $news_query, // WP_Query本体
    range: 5, // ページネーションの番号を表示する長さ
    prev_text: "←", // 戻るボタンのテキスト
    next_text: "→" // 進むボタンのテキスト
  );
  ?>
<?php endif; ?>
<?php wp_reset_postdata(); ?>
