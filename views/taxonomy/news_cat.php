<?php
$query = wpquery()
    ->setPostType(['news'])
    ->setPerPage(10)
    ->setOrderByDate()
    // ->debug(); // ->build()せずに組み立てたargsのみデバッグ
    ->build();
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

    <?php the_breadcrumb() ?>

    <div class="l-content -under">
        <section class="p-news_archive">
            <div class="c-inner">
                <?php if ($query->have_posts()): ?>

                    <ul class="c-card_news">
                        <?php while ($query->have_posts()): ?>
                            <?php $query->the_post(); ?>
                            <?php
                            $post_id  = get_the_ID();
                            $news_cat = get_the_terms($post_id, 'news_cat');
                            ?>
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

                    <?php the_pagination($query, range: 5, prev_text: "← 前へ", next_text: "次へ →"); ?>

                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </section>
    </div>
</main>
