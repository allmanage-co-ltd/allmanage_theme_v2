<?php
if (is_local()) {
    /**
     * グローバルに呼び出せる汎用関数の一部サンプルです。
     * ざっくりこんな関数が呼べるんだなと把握いただければ削除してください。
     */

    // echo home();
    // echo theme_uri();
    // echo theme_dir();
    // echo img_dir();
    // echo url('company');

    // the_component('searchform', ['hoge' => $fuga]);

    // foreach (config('app.runtime.local') as $t) {
    //     echo $t . '<br>';
    // }

    // $query = wpquery()
    //     ->setPostType(['news'])
    //     ->setPerPage(10)
    //     ->setOrderByDate()
    //     // ->debug();
    //     ->build();
    // d($query);

    // slog()->info('test');

    // $post = db()->stmt('SELECT * FROM wp_posts WHERE ID = %d', [1])->get();
    // d($post);

    // $client = http_client();
    // $res    = $client->get(home() . '/wp-json/wp/v2/posts', []);
    // d($res[]);

    // http_sess()->set('allmanage', 'hoge');
    // http_sess()->debug();

    // pdf_writer(['key' => 'hoge'], 'sample.php', 'sample', false);
}
?>

<main class="p-home">
    <div class="c-inner">
        <?php the_component('searchform') ?>

        <div class="item-image">
            <img src="<?= img_dir() ?>/logo.png" alt="">
        </div>

    </div>
</main>