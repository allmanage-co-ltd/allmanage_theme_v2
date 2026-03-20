<?php

return [
    /**
     * テーマ起動時に boot() したいクラス一覧。
     *
     * - config への明示登録だけを起動条件にする
     * - 各クラスは BootableWpHookInterface を実装する
     */
    'hook_providers' => [
        \App\Packages\Csv\Hooks\ExportCsvHook::class,
        \App\Packages\Csv\Hooks\ImportCsvHook::class,
    ],

    /**
     * CSV パッケージ設定。
     */
    'csv' => [
        /**
         * CSV エクスポート実装。
         */
        'exporter' => [
            \App\UseCase\Csv\Export\ExportNewsCsv::class,
        ],

        /**
         * CSV インポート実装。
         */
        'importer' => [
            \App\UseCase\Csv\Import\ImportNewsCsv::class,
        ],
    ],
];
