<form class="c-searchform" method="get" action="<?= home() ?>">
  <div class="c-searchform__input">
    <input type="hidden" name="post_type" value="<?= esc_attr(get_post_type() ?: '') ?>">
    <input type="text" name="s" value="<?= get_search_query() ?>" placeholder="キーワードで検索">
  </div>
  <div class="c-searchform__btn">
    <button type="submit">
      <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="13.94" height="13.942" viewBox="0 0 13.94 13.942">
        <defs>
          <clipPath id="clip-path">
            <rect width="13.94" height="13.942" fill="none" />
          </clipPath>
        </defs>
        <g id="リピートグリッド_1" data-name="リピートグリッド 1" clip-path="url(#clip-path)">
          <g transform="translate(-2010.008 -29)">
            <path id="search" d="M13.75,12.054,11.036,9.339a.653.653,0,0,0-.463-.191h-.444a5.661,5.661,0,1,0-.98.98v.444a.653.653,0,0,0,.191.463l2.715,2.715a.651.651,0,0,0,.923,0l.771-.771A.657.657,0,0,0,13.75,12.054ZM5.663,9.149A3.485,3.485,0,1,1,9.149,5.663,3.483,3.483,0,0,1,5.663,9.149Z" transform="translate(2010.008 29)" />
          </g>
        </g>
      </svg>
    </button>
  </div>
</form>
