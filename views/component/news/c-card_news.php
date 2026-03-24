<?php
$query = $args['query'];
$post_cat_slug = 'news_cat';
?>

<?php if ($query->have_posts()): ?>
    <ul class="c-card_news">
        <?php while ($query->have_posts()): ?>
            <?php $query->the_post(); ?>
            <?php
            /**
             * ループ内変数
             */
            $post_id  = get_the_ID();
            $post_cat = get_the_terms($post_id, $post_cat_slug);
            $cat_name = (!is_wp_error($post_cat) && !empty($post_cat)) ? $post_cat[0]->name : 'お知らせ';
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
        $query,
        range: 5,
        prev_text: "←",
        next_text: "→"
    );
    ?>
<?php endif; ?>
<?php wp_reset_postdata(); ?>
