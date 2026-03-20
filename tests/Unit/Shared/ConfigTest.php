<?php

test('配列を取得できる', function () {
    expect(\App\Shared\Config::get('app.runtime.local'))->toBeArray();
});

test('文字列を取得できる', function () {
    expect(\App\Shared\Config::get('app.root'))->toBeString();
});

test('値が存在しない時にデフォルトが返る', function () {
    expect(\App\Shared\Config::get('app.XXXXXX', 'default'))->toBe('default');
});
