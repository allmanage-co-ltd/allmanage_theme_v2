<form class="c-searchform" method="get" action="<?= home() ?>">
    <div class="c-searchform__input">
        <input type="hidden" name="post_type" value="<?= esc_attr(get_post_type() ?: '') ?>">
        <input type="text" name="s" value="<?= get_search_query() ?>" placeholder="キーワードで検索">
    </div>
    <div class="c-searchform__btn">
        <button type="submit">
            <img src="<?= img_uri(); ?>/common/icon_search.svg" alt="">
        </button>
    </div>
</form>
