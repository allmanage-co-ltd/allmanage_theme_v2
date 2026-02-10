<?php
// if (is_local()) {

//グローバルに呼び出せる汎用関数のサンプルです。

// echo home();
// echo img_dir();
// echo theme_uri();
// echo theme_dir();
// echo img_dir();
// echo url('company');

// d(config('cms.client_menu.hidden'));

// slog()->info('test', config('assets.css'));

// $post = db()->stmt('SELECT * FROM wp_posts WHERE ID = %d', [1])->get()
// d($post);

// http_sess()->set('allmanage', 'hoge');
// http_sess()->debug();
// }

// pdf_writer(['key' => 'データが渡せます'], 'sample.php', 'sample', false);


?>

<main class="p-home">
    <div class="c-inner">
        <?php the_component('searchform') ?>

        <div class="item-image">
            <img src="<?= img_dir() ?>/logo.png" alt="">
        </div>

    </div>
</main>
