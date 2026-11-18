<?php

namespace App\Exports\Chop;

use App\Exports\EngineeringTableExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Workbook: the official cost analysis sheet plus a planned-vs-actual variance sheet. */
class CostAnalysisExport implements WithMultipleSheets
{
    public function __construct(
        protected CostAnalysisSheet $costAnalysis,
        protected EngineeringTableExport $variance,
    ) {}

    public function sheets(): array
    {
        return [$this->costAnalysis, $this->variance];
    }
}
