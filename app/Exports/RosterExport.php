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

class RosterExport implements FromView, ShouldAutoSize, WithEvents, WithStyles, WithTitle
{
    protected $employees;

    protected $dates;

    protected $rosterData;

    protected $shifts;

    protected $summary;

    protected $employeeSummaries;

    public function __construct($employees, $dates, $rosterData, $shifts, $summary, $employeeSummaries)
    {
        $this->employees = $employees;
        $this->dates = $dates;
        $this->rosterData = $rosterData;
        $this->shifts = $shifts;
        $this->summary = $summary;
        $this->employeeSummaries = $employeeSummaries;
    }

    public function view(): View
    {
        return view('exports.roster', [
            'employees' => $this->employees,
            'dates' => $this->dates,
            'rosterData' => $this->rosterData,
            'shifts' => $this->shifts,
            'summary' => $this->summary,
            'employeeSummaries' => $this->employeeSummaries,
        ]);
    }

    public function title(): string
    {
        return 'Employee Roster - '.$this->summary['month_name'];
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $this->getColumnLetter(count($this->dates) + 2);
        $lastRow = count($this->employees) + 3;

        return [
            // Title row
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Header row
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0d6efd'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $this->getColumnLetter(count($this->dates) + 2);
                $lastRow = count($this->employees) + 3;

                // Set landscape orientation
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // Set print margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setRight(0.25);
                $sheet->getPageMargins()->setLeft(0.25);
                $sheet->getPageMargins()->setBottom(0.5);

                // Merge title row
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");

                // Apply borders to data area
                $sheet->getStyle("A3:{$lastColumn}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Center align all data cells
                $sheet->getStyle("B4:{$lastColumn}{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Set row height for data rows
                for ($i = 4; $i <= $lastRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(25);
                }

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(25); // Employee name
                for ($i = 2; $i <= count($this->dates) + 1; $i++) {
                    $sheet->getColumnDimension($this->getColumnLetter($i))->setWidth(5);
                }
                $sheet->getColumnDimension($lastColumn)->setWidth(15); // Summary

                // Apply alternating row colors
                for ($i = 4; $i <= $lastRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("A{$i}:{$lastColumn}{$i}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'f8f9fa'],
                            ],
                        ]);
                    }
                }

                // Freeze panes
                $sheet->freezePane('B4');
            },
        ];
    }

    private function getColumnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $mod = ($columnNumber - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $columnNumber = (int) (($columnNumber - $mod) / 26);
        }

        return $letter;
    }
}
