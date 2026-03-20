<?php

namespace App\Shared;

/**---------------------------------------------
 * CSV読み込みクラス
 * ---------------------------------------------
 * - CSVファイルのI/O専用
 * - ヘッダー検証やデータ変換は呼び出し側で行う
 */
class CsvReader
{
    public function __construct(
        private readonly string $delimiter = ',',
        private readonly string $enclosure = '"',
        private readonly string $escape = '\\',
    ) {
        //
    }

    /**
     * CSVファイルを配列として読み込む
     */
    public function execute(string $path): array
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Csvファイルが存在しません: {$path}");
        }

        $fp = fopen($path, 'r');

        if (!$fp) {
            throw new \RuntimeException("Csvを開けません: {$path}");
        }

        try {
            $rows = [];

            while (($data = fgetcsv($fp, 0, $this->delimiter, $this->enclosure, $this->escape)) !== false) {
                $rows[] = $data;
            }

            return $rows;
        } finally {
            fclose($fp);
        }
    }
}
