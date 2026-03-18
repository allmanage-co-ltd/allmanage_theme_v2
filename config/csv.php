<?php

return [
    /**-----------------------------------
     * CSVエクスポートクラス一覧
     *
     * - CSVエクスポート対象のUseCaseクラスを定義する
     * - Hook側でこの配列をループして実行URLクエリを生成し、実行する
     * - 各クラスは AbstractExportCsv を継承していること
     *
     * 例:
     *   ?csv_export=news → ExportNewsCsv の handle が実行される
     *
     * ※ postType() の戻り値とクエリパラメータを一致させること
     *----------------------------------*/
    'exporter' => [
        \App\UseCase\Csv\Export\ExportNewsCsv::class,
    ],

    /**-----------------------------------
     * CSVインポートクラス一覧
     *
     * - CSVインポート処理用のUseCaseクラスを定義する
     * - Hook側でこの配列をループして実行URLクエリを生成し、実行する
     * - 各クラスは AbstractImportCsv を継承していること
     *
     * 例:
     *   ?import=news → ImportNewsCsv の handle が実行される
     *
     * ※ postType() の戻り値とクエリパラメータを一致させること
     *----------------------------------*/
    'importer' => [
        \App\UseCase\Csv\Import\ImportNewsCsv::class,
    ],
];
