<?php

namespace App\Livewire\Contracts;

use App\Models\contract_approvals;
use App\Models\contracts;
use App\Models\departments;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManageContracts extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $typeFilter = '';

    public $departmentFilter = '';

    public $expiryFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        //
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatingExpiryFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'typeFilter', 'departmentFilter', 'expiryFilter']);
    }

    public function deleteContract($id)
    {
        try {
            contracts::findOrFail($id)->delete();
            $this->dispatch('toaster', [
                'type' => 'success',
                'message' => 'Contract deleted successfully',
            ]);
        } catch (\Exception) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Error deleting contract',
            ]);
        }
    }

    public function initiateApproval($contractId)
    {
        try {
            $contract = contracts::findOrFail($contractId);

            // Create approval stages
            $stages = [
                ['stage' => 'Department Head', 'order' => 1],
                ['stage' => 'Procurement', 'order' => 2],
                ['stage' => 'Legal', 'order' => 3],
                ['stage' => 'Management', 'order' => 4],
            ];

            foreach ($stages as $stage) {
                contract_approvals::create([
                    'contract_id' => $contractId,
                    'approver_id' => Auth::id(), // This should be dynamic based on roles
                    'stage' => $stage['stage'],
                    'approver_name' => Auth::user()->name,
                    'status' => 'pending',
                ]);
            }

            $contract->update(['status' => 'pending_approval']);

            $this->dispatch('toaster', [
                'type' => 'success',
                'message' => 'Approval workflow initiated successfully',
            ]);
        } catch (\Exception) {
            $this->dispatch('toaster', [
                'type' => 'error',
                'message' => 'Error initiating approval workflow',
            ]);
        }
    }

    public function render()
    {
        $contracts = contracts::query()
            ->with(['department', 'vendor', 'addedBy', 'parties', 'documents', 'approvals'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('contract_number', 'like', '%'.$this->search.'%')
                        ->orWhere('title', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->when($this->departmentFilter, function ($query) {
                $query->where('department_id', $this->departmentFilter);
            })
            ->when($this->expiryFilter, function ($query) {
                $days = (int) $this->expiryFilter;
                $query->whereBetween('end_date', [now(), now()->addDays($days)]);
            })
            ->latest()
            ->paginate(10);

        $departments = departments::all();

        $stats = [
            'total' => contracts::count(),
            'active' => contracts::where('status', 'active')->count(),
            'expiring_soon' => contracts::whereBetween('end_date', [now(), now()->addDays(90)])->count(),
            'pending_approval' => contracts::where('status', 'pending_approval')->count(),
        ];

        return view('livewire.contracts.manage-contracts', [
            'contracts' => $contracts,
            'departments' => $departments,
            'stats' => $stats,
        ]);
    }
}
