<?php

namespace App\Exports\Chop;

use App\Reports\Chop\CostAnalysisReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * The official CHOP "COST ANALYSIS yyyy-yyyy" worksheet: objective, target, planned
 * intervention, its costed inputs, then a subtotal row in the source of fund's colour.
 */
class CostAnalysisSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    protected const LAST = 'M';

    protected const NUMBER = '#,##0.00';

    /** @var array<int, string> row number => fill colour of subtotal rows */
    protected array $subtotalRows = [];

    protected array $boldRows = [];

    protected int $tableEnd = 0;

    public function __construct(protected array $sheet, protected string $title, protected array $meta = []) {}

    public function title(): string
    {
        return 'Cost Analysis';
    }

    public function columnWidths(): array
    {
        return ['A' => 4, 'B' => 18, 'C' => 24, 'D' => 4, 'E' => 32, 'F' => 11, 'G' => 22, 'H' => 10, 'I' => 15, 'J' => 9, 'K' => 16, 'L' => 17, 'M' => 13];
    }

    public function array(): array
    {
        $rows = [
            [$this->title],
            ...array_map(fn ($line) => [$line], $this->meta),
            ['OBJECTIVE', '', 'TARGETS', 'S/N', 'Planned interventions for priority areas', 'GFC Code', 'Required inputs', 'Unit of description', 'Unit cost of input', 'Annual Budget Estimates', 'Estimates', 'Total Amount', 'Source of funds'],
            ['', '', '', '', '', '', 'GFC Code description', '', '', 'No of Units', '', '', ''],
        ];

        foreach ($this->sheet['objectives'] as $objective) {
            $firstOfObjective = true;

            foreach ($objective['targets'] as $target) {
                $firstOfTarget = true;

                foreach ($target['interventions'] as $intervention) {
                    $rows[] = [
                        $firstOfObjective ? $objective['number'] : '',
                        $firstOfObjective ? $objective['name'] : '',
                        $firstOfTarget ? $target['text'] : '',
                        $firstOfTarget ? $target['number'] : '',
                        $intervention['number'].'. '.$intervention['name'],
                        '', '', '', '', '', '', '', '',
                    ];
                    $firstOfObjective = $firstOfTarget = false;

                    foreach ($intervention['inputs'] as $input) {
                        $rows[] = ['', '', '', '', '', $input['gfc_code'], $input['name'], $input['unit'], $input['unit_cost'], $input['quantity'], $input['estimate'], '', ''];
                    }

                    $rows[] = ['', '', '', '', '', '', '', '', '', '', '', $intervention['total'], $intervention['source']];
                    $this->subtotalRows[count($rows)] = $intervention['colour'];
                }
            }
        }

        $rows[] = ['', '', '', '', 'GRAND TOTAL', '', '', '', '', '', '', $this->sheet['grand_total'], ''];
        $this->boldRows[] = count($rows);
        $this->tableEnd = count($rows);

        // Summary by source of fund
        $rows[] = ['']; // an empty array would be dropped and shift the rows below
        $rows[] = ['', '', '', '', 'SUMMARY BY SOURCE OF FUND', '', '', '', '', '', '', 'Total Amount', 'Share'];
        $this->boldRows[] = count($rows);
        foreach ($this->sheet['by_source'] as $source => $data) {
            $share = $this->sheet['grand_total'] > 0 ? round($data['total'] / $this->sheet['grand_total'] * 100, 1).'%' : '-';
            $rows[] = ['', '', '', '', $source, '', '', '', '', '', '', $data['total'], $share];
            $this->subtotalRows[count($rows)] = CostAnalysisReport::hexColour($data['colour']);
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->style($event->sheet->getDelegate());
            },
        ];
    }

    protected function style(Worksheet $sheet): void
    {
        $last = self::LAST;
        $headerTop = count($this->meta) + 2;
        $headerBottom = $headerTop + 1;

        // Title and meta lines
        $sheet->mergeCells("A1:{$last}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        for ($r = 2; $r < $headerTop; $r++) {
            $sheet->mergeCells("A{$r}:{$last}{$r}");
            $sheet->getStyle("A{$r}")->getFont()->setItalic(true)->setSize(9);
        }

        // Two-row header like the official template
        $sheet->mergeCells("A{$headerTop}:B{$headerBottom}");
        foreach (['C', 'D', 'E', 'F', 'H', 'I', 'K', 'L', 'M'] as $col) {
            $sheet->mergeCells("{$col}{$headerTop}:{$col}{$headerBottom}");
        }
        $sheet->getStyle("A{$headerTop}:{$last}{$headerBottom}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']],
        ]);

        // Body
        $body = "A{$headerTop}:{$last}{$this->tableEnd}";
        $sheet->getStyle($body)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A'.($headerBottom + 1).":{$last}{$this->tableEnd}")->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $sheet->getStyle('B'.($headerBottom + 1).':C'.$this->tableEnd)->getFont()->setBold(true);
        $sheet->getStyle('I'.($headerBottom + 1).':L'.$sheet->getHighestRow())->getNumberFormat()->setFormatCode(self::NUMBER);
        $sheet->getStyle('J'.($headerBottom + 1).':J'.$this->tableEnd)->getNumberFormat()->setFormatCode('General');

        foreach ($this->subtotalRows as $row => $colour) {
            $sheet->getStyle("A{$row}:{$last}{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colour]],
                'font' => ['bold' => true, 'color' => ['rgb' => CostAnalysisReport::textOn($colour)]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        foreach ($this->boldRows as $row) {
            $sheet->getStyle("A{$row}:{$last}{$row}")->getFont()->setBold(true);
        }
        $sheet->getStyle("A{$this->tableEnd}:{$last}{$this->tableEnd}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CFE2FF');

        // Print setup: landscape, fit to width, repeat header rows
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd($headerTop, $headerBottom);
        $sheet->freezePane('A'.($headerBottom + 1));
    }
}
