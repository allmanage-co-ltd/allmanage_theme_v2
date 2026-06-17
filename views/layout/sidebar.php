<?php
$news_cat = get_terms([
    'taxonomy' => 'news_cat',
    'hide_empty' => true,
]);
?>

<aside class="l-sidebar">
    <div class="l-sidebar__wrap">

        <div class="l-sidebar__item">
            <div class="l-sidebar__ttl c-ttl_bd">キーワードから探す</div>
            <?php the_component('searchform') ?>
        </div>

        <?php if (!empty($news_cat) && !is_wp_error($news_cat)): ?>
            <div class="l-sidebar__item">
                <div class="l-sidebar__ttl c-ttl_bd">経営課題から探す</div>
                <ul class="l-sidebar__catList">
                    <?php foreach ($news_cat as $nc): ?>
                        <li class="l-sidebar__cat"><a href="<?php echo esc_url(get_term_link($nc)); ?>"><?php echo esc_html($nc->name); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>
</aside>
