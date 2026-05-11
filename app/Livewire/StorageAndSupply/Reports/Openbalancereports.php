<?php

namespace App\Livewire\StorageAndSupply\Reports;

use App\Models\{GRNOpenBalance, Subdepartment};
use Livewire\{Component, WithPagination};

class Openbalancereports extends Component
{
    use WithPagination;

    public $from, $to, $store_id = '', $status = '', $stores;
    public $selectedBalance, $viewingItems = false;

    public function mount()
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
        $this->stores = Subdepartment::whereActive(true)->get(['id', 'name']);
    }

    public function render()
    {
        $query = GRNOpenBalance::query()
            ->with(['subdepartment', 'creator'])
            ->when($this->from, fn($q) => $q->whereDate('date', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('date', '<=', $this->to))
            ->when($this->store_id, fn($q) => $q->where('subdepartment_id', $this->store_id))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest('date');

        return view('livewire.storage-and-supply.reports.openbalancereports', [
            'balances' => $query->paginate(15)
        ]);
    }

    public function viewItems($balanceId)
    {
        $this->selectedBalance = GRNOpenBalance::with(['items.item', 'subdepartment', 'creator'])
            ->findOrFail($balanceId);
        $this->viewingItems = true;
        $this->dispatch('modal-show', 'items-modal');
    }

    public function closeItemsModal()
    {
        $this->viewingItems = false;
        $this->selectedBalance = null;
    }

    public function clearFilters()
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
        $this->store_id = '';
        $this->status = '';
        $this->resetPage();
    }

    public function updatingFrom()
    {
        $this->resetPage();
    }

    public function updatingTo()
    {
        $this->resetPage();
    }

    public function updatingStoreId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }
}
