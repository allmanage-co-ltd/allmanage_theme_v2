<?php

test('配列を取得できるか' , function() {
    expect(\App\Services\Config::get('app.runtime.local'))->toBeArray();
});

test('文字列を取得できるか' , function() {
    expect(\App\Services\Config::get('app.root'))->toBeString();
});

test('値が存在しない時にデフォルトを返すか' , function() {
    expect(\App\Services\Config::get('app.XXXXXX', 'default'))->toBe('default');
});
