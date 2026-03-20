<?php

use App\WordPress\Csv\Actions\ExportGetTermSlugsAction;

class FakeWpError {}

function taxonomy_exists(string $taxonomy): bool
{
    return in_array($taxonomy, $GLOBALS['csv_test_taxonomies'] ?? [], true);
}

function get_the_terms(int $post_id, string $taxonomy): array|false|FakeWpError
{
    return $GLOBALS['csv_test_terms'][$post_id][$taxonomy] ?? false;
}

function is_wp_error(mixed $value): bool
{
    return $value instanceof FakeWpError;
}

\test('存在しないタクソノミーの場合は空文字を返す', function () {
    $GLOBALS['csv_test_taxonomies'] = [];
    $GLOBALS['csv_test_terms'] = [];

    $actual = (new ExportGetTermSlugsAction())(10, 'news_cat');

    expect($actual)->toBe('');
});

\test('タームスラッグをカンマ区切りで返す', function () {
    $GLOBALS['csv_test_taxonomies'] = ['news_cat'];
    $GLOBALS['csv_test_terms'] = [
        10 => [
            'news_cat' => [
                (object) ['slug' => 'news'],
                (object) ['slug' => 'featured%20item'],
            ],
        ],
    ];

    $actual = (new ExportGetTermSlugsAction())(10, 'news_cat');

    expect($actual)->toBe('news,featured item');
});

\test('WP_Errorまたは空タームの場合は空文字を返す', function () {
    $GLOBALS['csv_test_taxonomies'] = ['news_cat'];
    $GLOBALS['csv_test_terms'] = [
        10 => ['news_cat' => new FakeWpError()],
        11 => ['news_cat' => []],
    ];

    expect((new ExportGetTermSlugsAction())(10, 'news_cat'))->toBe('');
    expect((new ExportGetTermSlugsAction())(11, 'news_cat'))->toBe('');
});
