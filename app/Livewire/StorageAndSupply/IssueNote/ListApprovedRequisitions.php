<?php

namespace App\Livewire\StorageAndSupply\IssueNote;

use App\Models\{StoreRequisition, Subdepartment};
use Livewire\{Component, WithPagination};

class ListApprovedRequisitions extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_date_from = '';
    public $filter_date_to = '';
    public $filter_dept_requesting = '';

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        $requisitions = StoreRequisition::with([
                'dept_requesting',
                'dept_issuing',
                'requester',
                'requisition_items.item',
            ])
            ->withCount('requisition_items')
            ->where('status', 'approved')
            ->when($selectedSubdepartmentId, function ($query) use ($selectedSubdepartmentId) {
                $query->where('dept_issueing_id', $selectedSubdepartmentId);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                      ->orWhereHas('dept_requesting', fn($sq) =>
                          $sq->where('name', 'like', "%{$this->search}%")
                      )
                      ->orWhereHas('requester', fn($sq) =>
                          $sq->where('name', 'like', "%{$this->search}%")
                      );
                });
            })
            ->when($this->filter_date_from, function ($query) {
                $query->whereDate('requisition_date', '>=', $this->filter_date_from);
            })
            ->when($this->filter_date_to, function ($query) {
                $query->whereDate('requisition_date', '<=', $this->filter_date_to);
            })
            ->when($this->filter_dept_requesting, function ($query) {
                $query->where('dept_reqesting_id', $this->filter_dept_requesting);
            })
            ->orderBy('requisition_date', 'desc')
            ->paginate(15);

        $departments = Subdepartment::whereHas('department', function ($query) {
            $query->whereIn('nature_of_department', ['Pharmacy', 'Storage And Supply']);
        })
        ->orderBy('name')
        ->get();

        return view('livewire.storage-and-supply.issue-note.list-approved-requisitions', [
            'requisitions' => $requisitions,
            'departments' => $departments,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function updatingFilterDeptRequesting()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filter_date_from = '';
        $this->filter_date_to = '';
        $this->filter_dept_requesting = '';
        $this->resetPage();
    }

    public function createIssueNote($requisitionId)
    {
        $this->redirect(route('create-issue-note', ['requisitionId' => $requisitionId]), navigate: true);
    }
}
