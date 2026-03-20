<?php

return [
    'exporter' => [
        \App\Project\ExportNewsCsv::class,
    ],
    'importer' => [
        \App\Project\ImportNewsCsv::class,
    ],
];
