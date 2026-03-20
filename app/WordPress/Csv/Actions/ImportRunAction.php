<?php

namespace App\WordPress\Csv\Actions;

use App\WordPress\Csv\Enums\ImportColumnActionEnum;
use App\WordPress\Csv\Infrastructure\CsvReader;
use App\Support\Html;

/**---------------------------------------------
 * CSVインポート オーケストレーター
 * ---------------------------------------------
 * ImportCsv::handle() から呼び出される内部クラス。
 * 実装者（サブクラス）からは直接見えない。
 *
 * 責務:
 *   - CSVファイルの読み込み・パース
 *   - ヘッダー検証
 *   - 行ループとdryRunログ収集
 *
 * 投稿保存・アクション実行・型変換・添付解決は
 * それぞれ専用の invokable クラスに委譲する:
 *   ImportPostSaveAction          … SavePost（投稿作成・更新）
 *   ImportColumnAction            … UpdateMeta / SetTerms / SetThumbnail
 *   ImportValueConvertAction      … Bool / Array / Gallery / Text 型変換
 *   ImportAttachmentResolveAction … URL → attachment_id 解決
 */
class ImportRunAction
{
    public function __construct(
        private readonly CsvReader $reader,
        private readonly string $postType,
        private readonly array  $map,
        private readonly bool   $isDryRun,
    ) {
        //
    }

    public function run(): void
    {
        $rows = $this->rows();

        if ($rows === []) {
            throw new \RuntimeException('CSVファイルが空です');
        }

        $header = array_shift($rows);

        if (!$this->isValidHeader($header ?? [])) {
            throw new \RuntimeException('CSVヘッダーが一致しません');
        }

        $save      = new ImportPostSaveAction($this->postType, $this->map, $this->isDryRun);
        $action    = new ImportColumnAction($this->isDryRun);
        $converter = new ImportValueConvertAction();
        $resolver  = new ImportAttachmentResolveAction();

        $log = [];

        foreach ($rows as $index => $row) {

            $row = $this->mapRow($row);

            if ($row === null) {
                continue;
            }

            $post_id = $save($row);

            $rowLog  = [
                'row' => $index + 1,
                'post_id' => $post_id,
                'data' => [],
                'thumbnail' => null,
            ];

            foreach ($this->map as $key => $config) {

                if (($config['action'] ?? null) === ImportColumnActionEnum::SavePost) {
                    continue;
                }

                $raw   = $row[$key] ?? '';
                $value = $converter($raw, $config);

                $rowLog['data'][$key] = [
                    'value'  => $value,
                    'action' => $config['action']->value ?? '',
                ];

                if ($key === 'post_thumbnail') {
                    $rowLog['thumbnail'] = [
                        'raw'           => $raw,
                        'value'         => $value,
                        'attachment_id' => $resolver($value),
                    ];
                }

                $action($post_id, $key, $value, $config);
            }

            $log[] = $rowLog;
        }

        if ($this->isDryRun) {
            Html::pre($log);
            exit;
        }
    }

    // ------------------------
    // CSVパース
    // ------------------------

    /**
     * アップロードされたCSVファイルを読み込む
     *
     * - $_FILES['csv']['tmp_name'] からパスを取得する
     * - ファイルが選択されていない場合は RuntimeException を投げる
     */
    private function rows(): array
    {
        $path = $_FILES['csv']['tmp_name'] ?? '';

        if ($path === '') {
            throw new \RuntimeException('CSVファイルを選択してください');
        }

        return $this->reader->execute($path);
    }

    /**
     * ヘッダー行を検証する
     *
     * - map() に定義されたすべてのキーがCSVヘッダーに存在するか確認する
     * - 順番は問わない
     */
    private function isValidHeader(array $header): bool
    {
        $header = $this->normalize($header);

        foreach (array_keys($this->map) as $key) {
            if (!in_array($key, $header, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * データ行をmapに合わせて整形する
     *
     * - 空行はスキップ（null を返す）
     * - カラム数が不足している場合は空文字で補う
     * - カラム数が多い場合は切り捨てる
     * - ヘッダーキーと対応付けた連想配列で返す
     */
    private function mapRow(array $row): ?array
    {
        if ($this->isEmptyRow($row)) {
            return null;
        }

        $headers    = array_keys($this->map);
        $normalized = $this->normalize($row);
        $adjusted   = array_slice(
            array_pad($normalized, count($headers), ''),
            0,
            count($headers)
        );

        return array_combine($headers, $adjusted) ?: null;
    }

    /**
     * 行データを正規化する
     *
     * - BOM（\xEF\xBB\xBF）を除去する
     * - 前後の空白を除去する
     */
    private function normalize(array $row): array
    {
        return array_map(function ($value) {
            $value = is_scalar($value) ? (string) $value : '';

            if (str_starts_with($value, "\xEF\xBB\xBF")) {
                $value = substr($value, 3);
            }

            return trim($value);
        }, $row);
    }

    /**
     * 空行判定
     *
     * - 正規化後にすべてのセルが空文字の場合は空行とみなす
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($this->normalize($row) as $value) {
            if ($value !== '') {
                return false;
            }
        }
        return true;
    }
}
