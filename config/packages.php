<?php

return [
    /**
     * CMSフック登録クラス一覧
     *
     * - WordPressへフックを登録したいクラスを列挙する
     * - 各クラスは BootableInterface を継承し boot() を実装すること
     *
     * ■ 注意
     * - ここに登録していないPackages内のフック処理は一切動かない
     */
    'hook_providers' => [
        \App\Packages\Csv\Hooks\ExportCsvHook::class,
        \App\Packages\Csv\Hooks\ImportCsvHook::class,
    ],

    /**
     * CSVインエクスポーターパッケージの設定
     */
    'csv' => [
        /**
         * CSVダウンロード処理を行うUseCaseクラスを登録
         * 各クラスは ExportCsv を継承すること
         */
        'exporter' => [
            \App\UseCase\Csv\Export\ExportNewsCsv::class,
        ],

        /**
         * CSVアップロード処理を行うUseCaseクラスを登録
         * 各クラスは ImportCsv を継承すること
         */
        'importer' => [
            \App\UseCase\Csv\Import\ImportNewsCsv::class,
        ],
    ],
];
