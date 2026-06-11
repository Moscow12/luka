<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FpAttendanceExport implements FromView, ShouldAutoSize, WithEvents, WithStyles, WithTitle
{
    public function __construct(
        protected $rows,
        protected $dates,
        protected string $dateFrom,
        protected string $dateTo,
    ) {}

    public function view(): View
    {
        return view('exports.fp-attendance', [
            'rows'     => $this->rows,
            'dates'    => $this->dates,
            'dateFrom' => $this->dateFrom,
            'dateTo'   => $this->dateTo,
        ]);
    }

    public function title(): string
    {
        return 'Attendance '.$this->dateFrom.' to '.$this->dateTo;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->columnLetter(3 + $this->dates->count() + 2); // #, FP ID, Name, dates…, Rate
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0d6efd']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $totalCols = 3 + $this->dates->count() + 1; // #, FP ID, Name, date cols, Rate
                $lastCol   = $this->columnLetter($totalCols);
                $dataRows  = count($this->rows);
                $lastRow   = $dataRows + 3;

                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.5)->setRight(0.25)->setLeft(0.25)->setBottom(0.5);

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle("A3:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);

                $sheet->getStyle("D4:{$lastCol}{$lastRow}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                for ($i = 4; $i <= $lastRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(22);
                    if ($i % 2 === 0) {
                        $sheet->getStyle("A{$i}:{$lastCol}{$i}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f8f9fa']],
                        ]);
                    }
                }

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(28);
                for ($c = 4; $c <= 3 + $this->dates->count(); $c++) {
                    $sheet->getColumnDimension($this->columnLetter($c))->setWidth(22);
                }
                $sheet->getColumnDimension($lastCol)->setWidth(16);

                $sheet->freezePane('D4');
            },
        ];
    }

    private function columnLetter(int $n): string
    {
        $letter = '';
        while ($n > 0) {
            $mod    = ($n - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $n      = (int) (($n - $mod) / 26);
        }

        return $letter;
    }
}
