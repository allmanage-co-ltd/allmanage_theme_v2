<?php
$current_term  = get_queried_object();
$term_slug     = $current_term->slug;
$is_column_cat = is_tax($tax);
$is_taxonomy   = $is_column_cat || $is_column_tag;
$taxonomy_name = '';

if ($is_column_cat) {
  $taxonomy_name = $tax;
}

$query = wpquery()
  ->setPostType(['news'])
  ->setPerPage(10)
  ->setOrderByDate();

if ($is_taxonomy && $current_term) {
  $query = $query->setTaxQuery($taxonomy_name, [$term_slug]);
}

$query = $query->build();
?>

<main class="p-news -archive">

  <div class="p-kv_under">
    <div class="p-kv_under__inner">
      <div class="c-inner">
        <div class="p-kv_under__ttl">
          <div class="en">NEWS</div>
          <div class="jp">お知らせ</div>
        </div>
      </div>
    </div>
  </div>

  <div class="c-inner">
    <?php the_breadcrumb(); ?>
  </div>

  <section class="l-content -under">
    <div class="c-inner">
      <?php if ($query->have_posts()): ?>
      <ul class="c-card_news">
        <?php while ($query->have_posts()): ?>
        <?php $query->the_post(); ?>
        <?php $news_cat = get_the_terms(get_the_ID(), 'news_cat'); ?>
        <li class="c-card_news__item">
          <a href="<?php the_permalink(); ?>" class="c-card_news__link">
            <div class="c-card_news__info">
              <time datetime="<?php the_time('Y-m-d H:i:s'); ?>" class="c-card_news__date">
                <?php the_time('Y/m/d'); ?>
              </time>
              <?php if ($news_cat): ?>
              <div class="c-card_news__term"><?php echo $news_cat[0]->name; ?></div>
              <?php else: ?>
              <div class="c-card_news__term">お知らせ</div>
              <?php endif; ?>
            </div>
            <div class="c-card_news__ttl"><?php the_title(); ?></div>
          </a>
        </li>
        <?php endwhile; ?>
      </ul>
      <?php the_pagination($query); ?>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    </div>
  </section>
</main>