<?php

namespace App\Helpers;

/**---------------------------------------------
 * HTMLラッパー系のヘルパー
 * ---------------------------------------------
 */
class Html
{
    private static bool $styleLoaded = false;

    /**
     * JSON整形出力
     */
    public static function pre(mixed $data): void
    {
        self::style();

        $json = \json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            echo '<pre class="z-pre" style="color:red;">JSON encode error</pre>';
            return;
        }

        echo '<pre class="z-pre">';
        echo \htmlspecialchars($json, ENT_QUOTES, 'UTF-8');
        echo '</pre>';
    }

    /**
     * dumpして終了
     */
    public static function dd(mixed $data): void
    {
        self::style();

        echo '<pre class="z-pre">';
        echo \htmlspecialchars(var_export($data, true), ENT_QUOTES, 'UTF-8');
        echo '</pre>';

        exit;
    }

    /**
     * テーブル表示
     */
    public static function table(array $rows): void
    {
        self::style();

        if (empty($rows)) {
            echo '<p>データなし</p>';
            return;
        }

        echo '<table class="z-table">';

        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                if (\is_array($cell)) {
                    $cell = \json_encode($cell, JSON_UNESCAPED_UNICODE);
                }
                echo '<td>' . \htmlspecialchars((string)$cell) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table>';
    }

    /**
     * 共通スタイル出力（1回だけ）
     */
    private static function style(): void
    {
        if (self::$styleLoaded) {
            return;
        }

        self::$styleLoaded = true;

        echo <<<HTML
        <style>
        body{margin:0;}

        .z-pre {
            background:#0e1114;
            color:#c8d0d8;
            padding:16px;
            border:1px solid #1e2a2e;
            font-size:14px;
            overflow:auto;
            margin:0;
        }

        .z-table {
            width:100%;
            border-collapse:collapse;
            font-size:12px;
            background:#0e1114;
            color:#c8d0d8;
        }

        .z-table th {
            border:1px solid #1e2a2e;
            padding:6px;
            color:#00e5ff;
        }

        .z-table td {
            border:1px solid #1e2a2e;
            padding:6px;
        }
        </style>
        HTML;
    }
}
