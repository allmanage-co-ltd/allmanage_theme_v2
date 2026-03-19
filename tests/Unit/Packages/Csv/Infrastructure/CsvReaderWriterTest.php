<?php

use App\Packages\Csv\Infrastructure\CsvReader;
use App\Packages\Csv\Infrastructure\CsvWriter;

test('CsvWriterで書き込んだ内容をCsvReaderで読み込める', function () {
    $path = tempnam(sys_get_temp_dir(), 'csv_rw_');

    expect($path)->not->toBeFalse();

    try {
        $rows = [
            ['ID', 'Title', 'Flags'],
            [1, 'Hello', ['a', 'b']],
            [2, 'World', true],
            [3, null, false],
        ];

        (new CsvWriter())->execute($rows, path: $path);

        $actual = (new CsvReader())->execute(path: $path);

        expect($actual)->toBe([
            ['ID', 'Title', 'Flags'],
            ['1', 'Hello', 'a,b'],
            ['2', 'World', '1'],
            ['3', '', '0'],
        ]);
    } finally {
        if ($path !== false && file_exists($path)) {
            unlink($path);
        }
    }
});

test('CsvWriterはUTF-8出力時にBOMを書き込める', function () {
    $path = tempnam(sys_get_temp_dir(), 'csv_bom_');

    expect($path)->not->toBeFalse();

    try {
        (new CsvWriter(withBom: true))->execute([
            ['header'],
            ['value'],
        ], path: $path);

        $contents = file_get_contents($path);

        expect($contents)->not->toBeFalse();
        expect(bin2hex(substr($contents, 0, 3)))->toBe('efbbbf');
    } finally {
        if ($path !== false && file_exists($path)) {
            unlink($path);
        }
    }
});
