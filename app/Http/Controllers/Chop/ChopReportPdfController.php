<?php

namespace App\Http\Controllers\Chop;

use App\Http\Controllers\Controller;
use App\Reports\Chop\CostAnalysisReport;
use App\Reports\Chop\MonitoringReport;
use App\Support\Pdf\PdfReport;
use Illuminate\Http\Request;

/**
 * CHOP Cost Analysis and Monitoring & Evaluation printouts. Opens in the browser for
 * printing, or downloads with ?download=1. Filters match the on-screen pages.
 */
class ChopReportPdfController extends Controller
{
    public function __invoke(Request $request, string $report)
    {
        $user = $request->user();
        abort_unless($user->isSuperAdmin() || $user->can('manage-chop'), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'financial_year' => ['nullable', 'string', 'exists:financial_years,id'],
            'category' => ['nullable', 'string', 'exists:chopcategoryareas,id'],
            'source' => ['nullable', 'string', 'exists:sourceoffunds,id'],
            'activity_type' => ['nullable', 'string', 'in:expenditure,revenue'],
        ]);

        [$pdf, $filename] = match ($report) {
            'cost-analysis' => [self::costAnalysisPdf(CostAnalysisReport::fromArray($filters)), 'chop-cost-analysis'],
            'monitoring' => [self::monitoringPdf(MonitoringReport::fromArray($filters)), 'chop-monitoring'],
            default => abort(404),
        };

        $filename .= '-'.now()->format('Ymd');

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }

    public static function costAnalysisPdf(CostAnalysisReport $report): PdfReport
    {
        $activities = $report->activities();

        return PdfReport::make('exports.chop.cost-analysis-pdf', [
            'activities' => $activities,
            'stats' => $report->stats($activities),
            'filters' => $report->filterSummary(),
        ])->landscape()->footer('CHOP Cost Analysis');
    }

    public static function monitoringPdf(MonitoringReport $report): PdfReport
    {
        return PdfReport::make('exports.chop.monitoring-pdf', [
            'activities' => $report->activities(),
            'months' => MonitoringReport::months($report->financialYear()),
            'stats' => $report->stats(),
            'filters' => $report->filterSummary(),
        ])->landscape()->footer('CHOP Monitoring & Evaluation');
    }
}
