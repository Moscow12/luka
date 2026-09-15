<?php

namespace App\Livewire\Contracts;

use App\Models\contracts;
use App\Models\departments;
use App\Models\vendors;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ContractForm extends Component
{
    public $contractId;

    public $modalMode = 'create';

    // Contract fields
    public $contract_number;

    public $title;

    public $department_id;

    public $vendor_id;

    public $type;

    public $start_date;

    public $end_date;

    public $contract_value = 0;

    public $status = 'draft';

    public $description;

    public $notification_time = '90';

    public function mount($contractId = null)
    {
        if ($contractId) {
            $this->modalMode = 'edit';
            $this->contractId = $contractId;
            $this->loadContract();
        } else {
            // Auto-generate contract number for new contracts
            $this->contract_number = $this->generateContractNumber();
        }
    }

    public function loadContract()
    {
        $contract = contracts::findOrFail($this->contractId);

        $this->contract_number = $contract->contract_number;
        $this->title = $contract->title;
        $this->department_id = $contract->department_id;
        $this->vendor_id = $contract->vendor_id;
        $this->type = $contract->type;
        $this->start_date = $contract->start_date->format('Y-m-d');
        $this->end_date = $contract->end_date->format('Y-m-d');
        $this->contract_value = $contract->contract_value;
        $this->status = $contract->status;
        $this->description = $contract->description;
        $this->notification_time = $contract->notification_time;
    }

    public function generateContractNumber()
    {
        $year = now()->year;
        $lastContract = contracts::whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastContract) {
            $lastNumber = (int) substr($lastContract->contract_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'CNT-'.$year.'-'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        $rules = [
            'contract_number' => 'required|string|unique:contracts,contract_number'.($this->modalMode === 'edit' ? ','.$this->contractId : ''),
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'type' => 'required|in:supplier,service_provider,agency,partner',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'contract_value' => 'required|numeric|min:0',
            'status' => 'required|in:draft,active,expired,terminated,pending_approval',
            'notification_time' => 'required|in:90,60,30',
        ];

        $this->validate($rules);

        try {
            $data = [
                'contract_number' => $this->contract_number,
                'title' => $this->title,
                'department_id' => $this->department_id,
                'vendor_id' => $this->vendor_id,
                'type' => $this->type,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'contract_value' => $this->contract_value,
                'status' => $this->status,
                'description' => $this->description,
                'notification_time' => $this->notification_time,
                'added_by' => Auth::id(),
            ];

            if ($this->modalMode === 'create') {
                $contract = contracts::create($data);
                $message = 'Contract created successfully';
            } else {
                $contract = contracts::findOrFail($this->contractId);
                $contract->update($data);
                $message = 'Contract updated successfully';
            }

            $this->dispatch('toaster', ['type' => 'success', 'message' => $message]);

            return redirect()->route('contracts.view', ['contractId' => $contract->id]);
        } catch (\Exception $e) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error saving contract: '.$e->getMessage()]);
        }
    }

    public function cancel()
    {
        return redirect()->route('contracts.list');
    }

    public function render()
    {
        $departments = departments::where('status', 'active')->get();
        $vendors = vendors::where('status', 'active')->get();

        return view('livewire.contracts.contract-form', [
            'departments' => $departments,
            'vendors' => $vendors,
        ]);
    }
}
