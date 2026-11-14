<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Any engineering list or report as a worksheet: a header block (hospital, title, filters,
 * who generated it and when), then the table. Optional per-cell shading carries the
 * red / yellow / green of the on-screen report into Excel.
 */
class EngineeringTableExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $meta  lines printed under the title
     * @param  array<int, array<int, string>>  $fills  [row index => [column index => RRGGBB]]
     */
    public function __construct(
        protected string $title,
        protected array $headings,
        protected array $rows,
        protected array $meta = [],
        protected array $fills = [],
    ) {}

    public function array(): array
    {
        return [
            [config('app.name')],
            [$this->title],
            ...array_map(fn (string $line) => [$line], $this->meta),
            [],
            $this->headings,
            ...$this->rows,
        ];
    }

    public function title(): string
    {
        // Excel sheet names: max 31 characters, no slashes
        return mb_substr(str_replace(['/', '\\', '?', '*', '[', ']', ':'], ' ', $this->title), 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $headingRow = count($this->meta) + 4;
        $lastColumn = Coordinate::stringFromColumnIndex(max(1, count($this->headings)));

        $sheet->getStyle("A{$headingRow}:{$lastColumn}{$headingRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']],
        ]);

        foreach ($this->fills as $rowIndex => $columns) {
            foreach ($columns as $columnIndex => $colour) {
                $cell = Coordinate::stringFromColumnIndex($columnIndex + 1).($headingRow + 1 + $rowIndex);
                $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colour);
            }
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
