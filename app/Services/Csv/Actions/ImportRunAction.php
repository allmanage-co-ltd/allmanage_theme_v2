<?php

namespace App\Services\Csv\Actions;

use App\Enums\CsvColumnActionEnum;
use App\Services\Csv\CsvReader;
use App\Helpers\Html;

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
    /** @var callable(array): void|null */
    private readonly mixed  $onProgress = null,
  ) {
    //
  }

  public function run(): void
  {
    $rows = $this->rows();

    if ($rows === []) {
      throw new \RuntimeException('CSVファイルが空です');
    }

    $rawHeader = array_shift($rows);
    $header    = $this->normalize($rawHeader ?? []);

    if (!$this->isValidHeader($header)) {
      throw new \RuntimeException('CSVヘッダーが一致しません');
    }

    // 空行を除いた実行対象行数を事前計算する
    $total = \count(\array_filter($rows, fn(array $r) => !$this->isEmptyRow($r)));

    $save      = new ImportPostSaveAction($this->postType, $this->map, $this->isDryRun);
    $action    = new ImportColumnAction($this->isDryRun);
    $converter = new ImportValueConvertAction();
    $resolver  = new ImportAttachmentResolveAction($this->isDryRun);

    $log      = [];
    $processed = 0;

    foreach ($rows as $index => $row) {

      $row = $this->mapRow($row, $header);

      if ($row === null) {
        continue;
      }

      $processed++;
      $post_id = $save($row);

      $rowLog  = [
        'row'     => $index + 1,
        'post_id' => $post_id,
        'title'   => $row['post_title'] ?? '',
        'data'    => [],
        'thumbnail' => null,
      ];

      foreach ($this->map as $key => $config) {

        if (($config['action'] ?? null) === CsvColumnActionEnum::SavePost) {
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

      // 1行処理するたびに進捗コールバックを呼ぶ
      if ($this->onProgress !== null) {
        ($this->onProgress)([
          'processed' => $processed,
          'total'     => $total,
          'row'       => $rowLog,
        ]);
      }
    }

    if ($this->isDryRun && $this->onProgress === null) {
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
  /**
   * 正規化済みヘッダーにmap()の全キーが含まれるか確認する
   */
  private function isValidHeader(array $header): bool
  {
    foreach (\array_keys($this->map) as $key) {
      if (!\in_array($key, $header, true)) {
        return false;
      }
    }

    return true;
  }

  /**
   * データ行をCSVヘッダー順でキー付け連想配列に変換する
   *
   * - 空行はスキップ（null を返す）
   * - CSVのヘッダー行を基準に列を対応付けるため、map()のキー順に依存しない
   * - map()に存在しないCSVカラムは無視する
   */
  private function mapRow(array $row, array $csvHeader): ?array
  {
    if ($this->isEmptyRow($row)) {
      return null;
    }

    $normalized = $this->normalize($row);
    $adjusted   = \array_pad($normalized, \count($csvHeader), '');

    // CSVヘッダー順でキー付け
    $keyed = \array_combine($csvHeader, \array_slice($adjusted, 0, \count($csvHeader)));

    if ($keyed === false) {
      return null;
    }

    // map()に定義されたキーのみ抽出して返す
    $result = [];
    foreach (\array_keys($this->map) as $key) {
      $result[$key] = $keyed[$key] ?? '';
    }

    return $result;
  }

  /**
   * 行データを正規化する
   *
   * - BOM（\xEF\xBB\xBF）を除去する
   * - 前後の空白を除去する
   */
  private function normalize(array $row): array
  {
    return \array_map(function ($value) {
      $value = \is_scalar($value) ? (string) $value : '';

      if (str_starts_with($value, "\xEF\xBB\xBF")) {
        $value = \substr($value, 3);
      }

      return \trim($value);
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
