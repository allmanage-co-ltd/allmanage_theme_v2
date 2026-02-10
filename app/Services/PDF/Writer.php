<?php

namespace App\Services\PDF;

use App\Helpers\Path;
use TCPDF;

/**---------------------------------------------
 * PDF Writer
 * ---------------------------------------------
 * - View + Data から PDF を生成する
 * - TCPDF の初期化と出力制御を担当
 */
class Writer
{
    // TCPDFのインスタンス
    private TCPDF $pdf;

    public function __construct(
        // viewsに渡すデータ
        private array $data,

        // viewsのファイル名
        private string $view_filename,

        // 出力ファイル名
        private string $output_name = 'output',

        // ダウンロードするか
        private bool $download = false,
    ) {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    }

    /**
     * 実行
     */
    public function execute()
    {
        $this->cleanBuffer();
        $this->setting();
        $this->pdf->AddPage();
        $this->pdf->writeHTML($this->renderView());
        $this->output();
    }

    /**
     * 出力バッファ初期化
     */
    private function cleanBuffer(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * PDF共通設定
     */
    private function setting(): void
    {
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetFont('kozminproregular', '', 10);
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);
    }

    /**
     * View を HTML として描画
     */
    private function renderView(): string
    {
        ob_start();
        extract($this->data, EXTR_SKIP);
        include Path::views('pdf/' . $this->view_filename);
        return ob_get_clean();
    }

    /**
     * PDF出力
     */
    private function output(): void
    {
        $mode = $this->download ? 'D' : 'I';
        $name = sprintf('%s_%s.pdf', $this->output_name, date('Ymd_His'));
        $this->pdf->Output($name, $mode);
        exit;
    }

}
