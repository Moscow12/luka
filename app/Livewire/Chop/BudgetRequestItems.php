<?php

namespace App\Livewire\Chop;

use App\Exports\EngineeringTableExport;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;
use App\Models\departments;
use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Every item requested across all department budget requests in one list:
 * search by item name, filter by department / financial year / status,
 * edit unit prices inline, see each department's budget totals, export to Excel.
 */
class BudgetRequestItems extends Component
{
    use WithPagination;

    public $search = '';

    public $filterDepartment = '';

    public $filterFinancialYear = '';

    public $filterStatus = '';

    public $editingItemId = null;

    public $editPrice = null;

    public function mount()
    {
        $this->authorizeAccess();

        $this->filterFinancialYear = FinancialYear::current()?->id ?? '';
    }

    protected function authorizeAccess(): void
    {
        $user = Auth::user();

        abort_unless(
            $user && ($user->isSuperAdmin() || $user->canAny(['manage-chop-budget-items', 'manage-chop'])),
            403
        );
    }

    public function updating($property)
    {
        if (in_array($property, ['search', 'filterDepartment', 'filterFinancialYear', 'filterStatus'], true)) {
            $this->resetPage();
            $this->cancelEdit();
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterDepartment', 'filterStatus']);
        $this->filterFinancialYear = FinancialYear::current()?->id ?? '';
        $this->resetPage();
    }

    /**
     * Items joined to their request, item and department, with the current filters applied.
     */
    protected function filteredQuery(): Builder
    {
        $search = trim((string) $this->search);

        return BudgetRequestItem::query()
            ->join('budget_requests as br', 'br.id', '=', 'budget_request_items.budget_request_id')
            ->join('chopitems as ci', 'ci.id', '=', 'budget_request_items.item_id')
            ->join('departments as d', 'd.id', '=', 'br.department_id')
            ->when($search !== '', fn ($q) => $q->where('ci.name', 'like', '%'.$search.'%'))
            ->when($this->filterDepartment, fn ($q) => $q->where('br.department_id', $this->filterDepartment))
            ->when($this->filterFinancialYear, fn ($q) => $q->where('br.financial_year_id', $this->filterFinancialYear))
            ->when($this->filterStatus, fn ($q) => $q->where('br.status', $this->filterStatus));
    }

    protected function itemsQuery(): Builder
    {
        return $this->filteredQuery()
            ->select([
                'budget_request_items.*',
                'ci.name as item_name',
                'ci.unit as item_unit',
                'd.name as department_name',
                'br.request_number',
                'br.status as request_status',
            ])
            ->orderBy('d.name')
            ->orderBy('ci.name');
    }

    /**
     * Budget totals per department for the current filters.
     */
    protected function departmentBudgets()
    {
        return $this->filteredQuery()
            ->toBase()
            ->selectRaw('d.id, d.name, COUNT(*) as items_count')
            ->selectRaw('SUM(budget_request_items.requested_quantity * COALESCE(budget_request_items.requested_price, 0)) as requested_total')
            ->selectRaw('SUM(COALESCE(budget_request_items.approved_quantity, 0) * COALESCE(budget_request_items.approved_price, 0)) as approved_total')
            ->groupBy('d.id', 'd.name')
            ->orderBy('d.name')
            ->get();
    }

    public function startEdit($itemId)
    {
        $this->authorizeAccess();

        $item = BudgetRequestItem::findOrFail($itemId);

        $this->resetErrorBag();
        $this->editingItemId = $item->id;
        $this->editPrice = $item->requested_price ?? 0;
    }

    public function cancelEdit()
    {
        $this->editingItemId = null;
        $this->editPrice = null;
        $this->resetErrorBag();
    }

    public function savePrice()
    {
        $this->authorizeAccess();

        $this->validate([
            'editPrice' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
        ], [], ['editPrice' => 'price']);

        DB::transaction(function () {
            $item = BudgetRequestItem::lockForUpdate()->findOrFail($this->editingItemId);
            $item->update(['requested_price' => $this->editPrice]);

            // Keep the request's estimated total in step with its items
            BudgetRequest::findOrFail($item->budget_request_id)->calculateTotal();
        });

        $this->cancelEdit();
        session()->flash('success', 'Price updated successfully.');
    }

    public function export()
    {
        $this->authorizeAccess();

        $items = $this->itemsQuery()->get();
        $statuses = BudgetRequest::getStatuses();

        $rows = $items->map(fn ($item) => [
            $item->department_name,
            $item->request_number,
            $statuses[$item->request_status] ?? $item->request_status,
            $item->item_name,
            $item->item_unit,
            (float) $item->requested_quantity,
            (float) ($item->requested_price ?? 0),
            (float) $item->requested_quantity * (float) ($item->requested_price ?? 0),
            $item->approved_quantity !== null ? (float) $item->approved_quantity : null,
            $item->approved_price !== null ? (float) $item->approved_price : null,
            (float) ($item->approved_quantity ?? 0) * (float) ($item->approved_price ?? 0),
        ])->all();

        // Department budget summary under the item list
        $budgets = $this->departmentBudgets();
        $rows[] = [];
        $rows[] = ['DEPARTMENT BUDGET SUMMARY'];
        $rows[] = ['Department', 'Items', '', '', '', '', '', 'Requested Total', '', '', 'Approved Total'];
        foreach ($budgets as $budget) {
            $rows[] = [$budget->name, (int) $budget->items_count, '', '', '', '', '', (float) $budget->requested_total, '', '', (float) $budget->approved_total];
        }
        $rows[] = ['GRAND TOTAL', (int) $budgets->sum('items_count'), '', '', '', '', '', (float) $budgets->sum('requested_total'), '', '', (float) $budgets->sum('approved_total')];

        $meta = array_filter([
            'Financial Year: '.($this->filterFinancialYear ? (FinancialYear::find($this->filterFinancialYear)?->name ?? '-') : 'All'),
            'Department: '.($this->filterDepartment ? (departments::find($this->filterDepartment)?->name ?? '-') : 'All'),
            'Status: '.($this->filterStatus ? ($statuses[$this->filterStatus] ?? $this->filterStatus) : 'All'),
            trim((string) $this->search) !== '' ? 'Search: '.trim($this->search) : null,
            'Generated by '.trim(Auth::user()->first_name.' '.Auth::user()->surname).' on '.now()->format('d M Y H:i'),
        ]);

        return Excel::download(
            new EngineeringTableExport(
                'Budget Request Items',
                ['Department', 'Request No.', 'Status', 'Item', 'Unit', 'Quantity', 'Unit Price', 'Total', 'Approved Qty', 'Approved Price', 'Approved Total'],
                $rows,
                array_values($meta),
            ),
            'budget_request_items_'.now()->format('Y_m_d_His').'.xlsx'
        );
    }

    public function render()
    {
        $budgets = $this->departmentBudgets();

        return view('livewire.chop.budget-request-items', [
            'items' => $this->itemsQuery()->paginate(25),
            'departmentBudgets' => $budgets,
            'grandRequested' => (float) $budgets->sum('requested_total'),
            'grandApproved' => (float) $budgets->sum('approved_total'),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
            'financialYears' => FinancialYear::orderBy('start_date', 'desc')->get(['id', 'name']),
            'statuses' => BudgetRequest::getStatuses(),
        ]);
    }
}
