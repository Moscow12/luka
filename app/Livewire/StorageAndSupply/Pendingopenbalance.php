<?php

namespace App\Livewire\StorageAndSupply;

use App\Models\GRNOpenBalance;
use Livewire\Component;

class Pendingopenbalance extends Component
{
    public $pending_balances = [];
    public $selected_balance = null;
    public $show_items = false;

    public function mount()
    {
        $this->loadPendingBalances();
    }

    public function render()
    {
        return view('livewire.storage-and-supply.pendingopenbalance');
    }

    public function loadPendingBalances()
    {
        $this->pending_balances = GRNOpenBalance::with(['subdepartment', 'items.item'])
            ->where('status', '!=', 'approved')
            ->orWhereNull('status')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function viewItems($id)
    {
        $this->selected_balance = GRNOpenBalance::with(['subdepartment', 'items.item'])
            ->find($id);

        if (!$this->selected_balance) {
            return $this->dispatch('error', 'Open balance not found.');
        }

        $this->show_items = true;
        $this->dispatch('modal-show', 'view-items-modal');
    }

    public function closeModal()
    {
        $this->show_items = false;
        $this->selected_balance = null;
    }

    public function approve($id)
    {
        $balance = GRNOpenBalance::find($id);

        if (!$balance) {
            return $this->dispatch('error', 'Open balance not found.');
        }

        if ($balance->items()->count() == 0) {
            return $this->dispatch('error', 'Cannot approve open balance with no items.');
        }

        $balance->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        $this->dispatch('success', 'Open balance approved successfully.');
        $this->loadPendingBalances();
        $this->closeModal();
    }
}
