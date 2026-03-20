<?php

return [
    /**
     * テーマ起動時に boot() するクラス一覧。
     *
     * 起動対象を設定に明示して、初期化の入口を追いやすくする。
     */
    'hook_providers' => [
        \App\WordPress\Csv\Hooks\ExportCsvHook::class,
        \App\WordPress\Csv\Hooks\ImportCsvHook::class,
    ],

    /**
     * CSV パッケージ設定。
     */
    'csv' => [
        'exporter' => [
            \App\UseCase\Csv\Export\ExportNewsCsv::class,
        ],
        'importer' => [
            \App\UseCase\Csv\Import\ImportNewsCsv::class,
        ],
    ],
];
