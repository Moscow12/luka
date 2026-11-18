<?php

namespace App\Http\Controllers\Chop;

use App\Http\Controllers\Controller;
use App\Models\FinancialYear;
use App\Models\sourceoffunds;
use App\Support\Pdf\PdfReport;
use Illuminate\Http\Request;

/**
 * Sources of Fund printout. ?download=1 saves the file; otherwise it opens in the
 * browser's PDF viewer for printing. Filters match the CHOP Setup list.
 */
class FundSourcesPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isSuperAdmin() || $user->canAny(['manage-chop', 'view-chop']), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'financial_year' => ['nullable', 'string', 'exists:financial_years,id'],
        ]);
        $search = $filters['search'] ?? null;
        $financialYearId = $filters['financial_year'] ?? null;

        $sources = sourceoffunds::query()
            ->with('financialYear')
            ->filtered($search, $financialYearId)
            ->orderBy('name')
            ->get();

        $financialYear = $financialYearId ? FinancialYear::find($financialYearId) : null;

        $report = PdfReport::make('exports.chop.fund-sources-pdf', [
            'sources' => $sources,
            'totalEstimatedCost' => (float) $sources->sum('estimated_cost_value'),
            'financialYearName' => $financialYear?->name ?? 'All financial years',
            'search' => $search,
        ])->landscape()->footer('Sources of Fund');

        $filename = 'sources-of-fund'.($financialYear ? '-'.$financialYear->name : '').'-'.now()->format('Ymd');

        return $request->boolean('download') ? $report->download($filename) : $report->stream($filename);
    }
}
