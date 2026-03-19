<?php

namespace App\Packages\Csv\Actions;

use App\Packages\Csv\Enums\ImportValueTypeEnum;

/**---------------------------------------------
 * CSV値の型変換
 * ---------------------------------------------
 * map() で指定された type に応じて CSV の生値を変換する invokable クラス。
 *
 * 変換種別:
 *   Text    … trim のみ（デフォルト）
 *   Bool    … true_values に一致すれば 1、それ以外は 0
 *   Array   … explode で配列化（URLデコードも行う）
 *   Gallery … explode してURLをattachment_idに変換した int[] を返す
 */
class ImportValueConvertAction
{
    /**
     * 値を変換して返す
     *
     * - type が指定されていない場合は TEXT として扱う
     * - type が ImportValueTypeEnum 以外の場合も TEXT にフォールバックする
     */
    public function __invoke(string $value, array $config): mixed
    {
        $type = $config['type'] ?? ImportValueTypeEnum::Text;

        if (!$type instanceof ImportValueTypeEnum) {
            $type = ImportValueTypeEnum::Text;
        }

        return match ($type) {
            ImportValueTypeEnum::Bool    => in_array($value, $config['true_values'] ?? ['1', 'true'], true) ? 1 : 0,
            ImportValueTypeEnum::Array   => $this->explode($value, $config),
            ImportValueTypeEnum::Gallery => $this->toAttachmentIds($value, $config),
            default                      => trim($value),
        };
    }

    /**
     * 区切り文字で配列化する
     *
     * - 空文字の場合は空配列を返す
     * - 各要素は trim + urldecode する
     * - 区切り文字は config['explode'] で指定（デフォルト ','）
     */
    private function explode(string $value, array $config): array
    {
        if ($value === '') {
            return [];
        }

        $delimiter = $config['explode'] ?? ',';

        return array_map(
            fn($v) => trim(urldecode($v)),
            explode($delimiter, $value)
        );
    }

    /**
     * ギャラリー用にattachment_idの配列へ変換する
     *
     * - explode で分割してURLを ImportAttachmentResolveAction で解決する
     * - 解決できなかったURLはスキップする
     */
    private function toAttachmentIds(string $value, array $config): array
    {
        $resolve = new ImportAttachmentResolveAction();
        $ids     = [];

        foreach ($this->explode($value, $config) as $file) {
            $id = $resolve($file);
            if ($id) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
