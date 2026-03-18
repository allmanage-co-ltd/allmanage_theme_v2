<?php

namespace App\UseCase\Csv\Import;

use App\Enums\Csv\CsvImportAction;
use App\Enums\Csv\CsvImportValueType;
use App\Support\Csv;
use RuntimeException;

/**---------------------------------------------
 * CSVインポート 基底クラス
 * ---------------------------------------------
 * - CSVファイルを読み込み投稿・メタ・タクソノミーを登録する共通処理を提供する
 * - カラム定義（map）と投稿タイプはサブクラスに委譲する
 * - dryRunにより実行せず検証のみ行うことが可能
 */
abstract class AbstractImportCsv
{
    /**
     * 投稿タイプを返す
     */
    abstract protected function postType(): string;

    /**
     * CSVマッピング定義
     *
     * - key: CSVカラム名（ヘッダーと一致する必要あり）
     * - action: 実行する処理
     *   - SAVE_POST: 投稿フィールドとして保存
     *   - UPDATE_META: post_meta更新
     *   - SET_TERMS: タクソノミー設定
     *   - SET_THUMBNAIL: アイキャッチ画像設定
     * - type: 値の変換タイプ
     *   - TEXT / BOOL / ARRAY / GALLERY
     */
    abstract protected function map(): array;

    /**
     * dryRunモード判定
     *
     * - true: DB更新せずログ出力のみ
     * - false: 実際に登録処理を行う
     */
    abstract protected function dryRun(): bool;

    /**
     * 実行権限を持つユーザーロールを指定
     */
    protected function auth(): bool
    {
        return true;
    }

    /**
     * インポートキーを取得（外部公開用）
     *
     * - URLクエリ (?import=xxx) と一致判定に使用する
     */
    public function key(): string
    {
        return $this->postType();
    }

    /**
     * 実行可能か判定（外部公開用）
     */
    public function can(): bool
    {
        return $this->auth();
    }

    /**
     * CSVヘッダーを取得
     *
     * - map() のキー順をそのまま使用する
     */
    private function header(): array
    {
        return array_keys($this->map());
    }

    /**
     * CSVインポート実行
     *
     * 処理フロー:
     * 1. CSV読み込み
     * 2. ヘッダー検証
     * 3. 各行を正規化して投稿データへ変換
     * 4. 投稿作成/更新
     * 5. 各カラムのアクション実行
     * 6. dryRun時はログのみ出力
     */
    public function handle(): void
    {
        $rows = $this->rows();

        if ($rows === []) {
            throw new RuntimeException('CSVファイルが空です');
        }

        // ヘッダー取得
        $header = array_shift($rows);

        // ヘッダー検証
        if (!$this->isValidHeader($header ?? [])) {
            throw new RuntimeException('CSVヘッダーが一致しません');
        }

        $map  = $this->map();
        $import_result_test = [];

        foreach ($rows as $index => $row) {

            // 行の正規化
            $row = $this->mapRow($row);

            if ($row === null) {
                continue;
            }

            // 投稿作成・更新
            $post_id = $this->savePost($row, $map);

            // 行単位ログ
            $rowLog = [
                'row' => $index + 1,
                'post_id' => $post_id,
                'data' => [],
                'thumbnail' => null,
            ];

            foreach ($map as $key => $config) {

                // 投稿保存系はスキップ
                if (($config['action'] ?? null) === CsvImportAction::SAVE_POST) {
                    continue;
                }

                // 値取得・変換
                $raw   = $row[$key] ?? '';
                $value = $this->convert($raw, $config);

                // 通常ログ
                $rowLog['data'][$key] = [
                    'value'  => $value,
                    'action' => $config['action']->value ?? '',
                ];

                // サムネイル検証ログ
                if ($key === 'post_thumbnail') {
                    $rowLog['thumbnail'] = [
                        'raw' => $raw,
                        'value' => $value,
                        'attachment_id' => $this->findAttachmentId($value),
                    ];
                }

                // アクション実行
                $this->runAction($post_id, $key, $value, $config);
            }

            $import_result_test[] = $rowLog;
        }

        // dryRun時はログ出力のみ
        if ($this->dryRun()) {
            echo '<pre>';
            print_r($import_result_test);
            echo '</pre>';
            exit;
        }
    }

    // ------------------------
    // 投稿保存
    // ------------------------

    /**
     * 投稿の作成・更新を行う
     *
     * - map() の SAVE_POST 指定カラムのみ対象
     * - post_id があれば更新、なければ新規作成
     */
    protected function savePost(array $row, array $map): int
    {
        $data = [
            'post_type' => $this->postType(),
        ];

        foreach ($map as $key => $config) {

            if (($config['action'] ?? null) !== CsvImportAction::SAVE_POST) {
                continue;
            }

            $value = trim((string) ($row[$key] ?? ''));

            if ($value === '') {
                continue;
            }

            $data[$key === 'post_id' ? 'ID' : $key] = $value;
        }

        $post_id = (int) ($data['ID'] ?? 0);

        // dryRun時は保存しない
        if ($this->dryRun()) {
            return 0;
        }

        $result = $post_id > 0
            ? wp_update_post($data, true)
            : wp_insert_post($data, true);

        if (is_wp_error($result)) {
            throw new RuntimeException($result->get_error_message());
        }

        return (int) $result;
    }

    // ------------------------
    // アクション実行
    // ------------------------

    /**
     * カラムごとの処理を実行する
     *
     * - UPDATE_META: post_meta更新
     * - SET_TERMS: タクソノミー設定
     * - SET_THUMBNAIL: アイキャッチ設定
     */
    private function runAction(int $post_id, string $key, mixed $value, array $config): void
    {
        $action = $config['action'] ?? null;

        // dryRun時は副作用を止める
        if ($this->dryRun()) {
            return;
        }

        if (!$action instanceof CsvImportAction) {
            return;
        }

        // メタ更新
        if ($action === CsvImportAction::UPDATE_META) {
            update_post_meta($post_id, $key, $value);
            return;
        }

        // タクソノミー設定
        if ($action === CsvImportAction::SET_TERMS) {

            $taxonomy = $config['taxonomy'] ?? null;

            if (!$taxonomy) {
                return;
            }

            wp_set_object_terms(
                $post_id,
                is_array($value) ? $value : [$value],
                $taxonomy
            );

            return;
        }

        // サムネイル設定
        if ($action === CsvImportAction::SET_THUMBNAIL) {

            if ($value === '') {
                return;
            }

            $id = $this->findAttachmentId($value);

            if ($id) {
                set_post_thumbnail($post_id, $id);
            }

            return;
        }
    }

    // ------------------------
    // 型変換
    // ------------------------

    /**
     * CSV値を型変換する
     *
     * - BOOL: 真偽値変換
     * - ARRAY: 区切り文字で配列化
     * - GALLERY: 添付ID配列に変換
     * - TEXT: そのまま
     */
    private function convert(string $value, array $config): mixed
    {
        $type = $config['type'] ?? CsvImportValueType::TEXT;

        if (!$type instanceof CsvImportValueType) {
            $type = CsvImportValueType::TEXT;
        }

        return match ($type) {

            CsvImportValueType::BOOL =>
            in_array($value, $config['true_values'] ?? ['1', 'true'], true) ? 1 : 0,

            CsvImportValueType::ARRAY =>
            $this->explode($value, $config),

            CsvImportValueType::GALLERY =>
            $this->toAttachmentIds($value, $config),

            default =>
            trim($value),
        };
    }

    /**
     * 区切り文字で配列化
     *
     * - URLエンコードも同時にデコードする
     */
    private function explode(string $value, array $config): array
    {
        if ($value === '') {
            return [];
        }

        $delimiter = $config['explode'] ?? ',';

        return array_map(function ($v) {
            return trim(urldecode($v));
        }, explode($delimiter, $value));
    }

    /**
     * ギャラリー用に添付ID配列へ変換
     */
    private function toAttachmentIds(string $value, array $config): array
    {
        $files = $this->explode($value, $config);

        $ids = [];

        foreach ($files as $file) {
            $id = $this->findAttachmentId($file);
            if ($id) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    // ------------------------
    // 共通
    // ------------------------

    /**
     * 添付ファイルIDを取得
     *
     * 優先順位:
     * 1. attachment_url_to_postid
     * 2. _wp_attached_file 完全一致
     */
    protected function findAttachmentId(string $url): int
    {
        $url = trim($url);

        if ($url === '') {
            return 0;
        }

        $id = attachment_url_to_postid($url);
        if ($id) {
            return $id;
        }

        $upload = wp_get_upload_dir();

        $relative = str_replace(
            $upload['baseurl'] . '/',
            '',
            $url
        );

        global $wpdb;

        $id = $wpdb->get_var($wpdb->prepare(
            "
            SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = '_wp_attached_file'
            AND meta_value = %s
            LIMIT 1
            ",
            $relative
        ));

        return (int) ($id ?? 0);
    }

    /**
     * CSV読み込み
     */
    private function rows(): array
    {
        $path = $_FILES['csv']['tmp_name'] ?? '';

        if ($path === '') {
            throw new RuntimeException('CSVファイルを選択してください');
        }

        return (new Csv(path: $path))->read();
    }

    /**
     * ヘッダー検証
     *
     * - map() に定義されたキーがすべて存在するか確認
     */
    private function isValidHeader(array $header): bool
    {
        $header = $this->normalize($header);

        foreach (array_keys($this->map()) as $key) {
            if (!in_array($key, $header, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 行データをmapに合わせて整形
     *
     * - 空行は除外
     * - カラム数を補正
     * - ヘッダーと対応付け
     */
    private function mapRow(array $row): ?array
    {
        if ($this->isEmptyRow($row)) {
            return null;
        }

        $normalized = $this->normalize($row);

        $adjusted = array_slice(
            array_pad($normalized, count($this->header()), ''),
            0,
            count($this->header())
        );

        return array_combine($this->header(), $adjusted) ?: null;
    }

    /**
     * 値の正規化
     *
     * - BOM除去
     * - trim
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
