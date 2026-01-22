<?php

namespace App\Livewire\Hr\Contract;

use App\Models\ContractRequest;
use App\Models\Employee;
use App\Models\Employeecontracts;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MyContractRequest extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Current employee data
    public $employee;

    public $currentContract;

    public $contractExpiringAlert = false;

    public $daysUntilExpiry = 0;

    // Request form
    public $showModal = false;

    public $editingId = null;

    public $request_type = 'renewal';

    public $proposed_start_date;

    public $proposed_end_date;

    public $extension_period;

    public $reason;

    public $last_working_day;

    public $handover_notes;

    public $employee_attachment;

    public $existing_attachment = null;

    // For viewing request details
    public $viewingRequest = null;

    public $showViewModal = false;

    public function mount()
    {
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->loadCurrentContract();
        }
    }

    protected function loadCurrentContract()
    {
        $this->currentContract = Employeecontracts::where('employee_id', $this->employee->id)
            ->where('status', 'active')
            ->first();

        if ($this->currentContract) {
            $this->daysUntilExpiry = now()->diffInDays($this->currentContract->expire_date, false);
            // Alert if contract expires within 90 days
            $this->contractExpiringAlert = $this->daysUntilExpiry <= 90 && $this->daysUntilExpiry > 0;
        }
    }

    public function openRequestModal($type = 'renewal')
    {
        $this->resetForm();
        $this->request_type = $type;

        if ($this->currentContract) {
            if ($type === 'renewal' || $type === 'extension') {
                $this->proposed_start_date = $this->currentContract->expire_date->addDay()->format('Y-m-d');
                $this->proposed_end_date = $this->currentContract->expire_date->addYear()->format('Y-m-d');
            } elseif ($type === 'termination') {
                // Default last working day to contract expire date or 30 days from now
                $this->last_working_day = min(
                    $this->currentContract->expire_date,
                    now()->addDays(30)
                )->format('Y-m-d');
            }
        }

        $this->showModal = true;
    }

    public function editRequest($id)
    {
        $request = ContractRequest::where('id', $id)
            ->where('employee_id', $this->employee->id)
            ->where('status', 'pending')
            ->first();

        if (! $request) {
            session()->flash('error', 'Request not found or cannot be edited.');

            return;
        }

        $this->resetForm();
        $this->editingId = $id;
        $this->request_type = $request->request_type;
        $this->proposed_start_date = $request->proposed_start_date?->format('Y-m-d');
        $this->proposed_end_date = $request->proposed_end_date?->format('Y-m-d');
        $this->extension_period = $request->extension_period;
        $this->reason = $request->reason;
        $this->last_working_day = $request->last_working_day?->format('Y-m-d');
        $this->handover_notes = $request->handover_notes;
        $this->existing_attachment = $request->employee_attachment;

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->reset([
            'request_type',
            'proposed_start_date',
            'proposed_end_date',
            'extension_period',
            'reason',
            'last_working_day',
            'handover_notes',
            'employee_attachment',
            'existing_attachment',
        ]);
        $this->request_type = 'renewal';
        $this->resetValidation();
    }

    protected function rules()
    {
        $rules = [
            'request_type' => 'required|in:renewal,extension,termination',
            'reason' => 'required|string|min:10|max:1000',
        ];

        if ($this->request_type === 'renewal') {
            $rules['proposed_start_date'] = 'required|date|after_or_equal:today';
            $rules['proposed_end_date'] = 'required|date|after:proposed_start_date';
        } elseif ($this->request_type === 'extension') {
            $rules['extension_period'] = 'required|integer|min:1|max:24';
        } elseif ($this->request_type === 'termination') {
            $rules['last_working_day'] = 'required|date|after_or_equal:today';
            $rules['handover_notes'] = 'nullable|string|max:2000';
            // Attachment is recommended for termination
            if (! $this->editingId || ! $this->existing_attachment) {
                $rules['employee_attachment'] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';
            } else {
                $rules['employee_attachment'] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';
            }
        }

        return $rules;
    }

    public function submitRequest()
    {
        $this->validate();

        // If editing, update existing request
        if ($this->editingId) {
            $request = ContractRequest::where('id', $this->editingId)
                ->where('employee_id', $this->employee->id)
                ->where('status', 'pending')
                ->first();

            if (! $request) {
                session()->flash('error', 'Request not found or cannot be edited.');
                $this->closeModal();

                return;
            }

            $data = [
                'request_type' => $this->request_type,
                'reason' => $this->reason,
            ];

            if ($this->request_type === 'renewal') {
                $data['proposed_start_date'] = $this->proposed_start_date;
                $data['proposed_end_date'] = $this->proposed_end_date;
                $data['extension_period'] = null;
                $data['last_working_day'] = null;
                $data['handover_notes'] = null;
            } elseif ($this->request_type === 'extension') {
                $data['extension_period'] = $this->extension_period;
                $data['proposed_start_date'] = $this->currentContract->expire_date->addDay();
                $data['proposed_end_date'] = $this->currentContract->expire_date->addMonths($this->extension_period);
                $data['last_working_day'] = null;
                $data['handover_notes'] = null;
            } elseif ($this->request_type === 'termination') {
                $data['last_working_day'] = $this->last_working_day;
                $data['handover_notes'] = $this->handover_notes;
                $data['extension_period'] = null;
                $data['proposed_start_date'] = null;
                $data['proposed_end_date'] = null;
            }

            // Handle attachment upload
            if ($this->employee_attachment) {
                $data['employee_attachment'] = $this->employee_attachment->store('contract-requests', 'public');
            }

            $request->update($data);
            session()->flash('success', 'Your request has been updated successfully.');
            $this->closeModal();

            return;
        }

        // Check if there's already a pending request (for new requests)
        $existingRequest = ContractRequest::where('employee_id', $this->employee->id)
            ->where('contract_id', $this->currentContract->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            session()->flash('error', 'You already have a pending request for this contract. Please edit the existing request or wait for HR to review it.');
            $this->closeModal();

            return;
        }

        $data = [
            'employee_id' => $this->employee->id,
            'contract_id' => $this->currentContract->id,
            'request_type' => $this->request_type,
            'reason' => $this->reason,
            'status' => 'pending',
        ];

        if ($this->request_type === 'renewal') {
            $data['proposed_start_date'] = $this->proposed_start_date;
            $data['proposed_end_date'] = $this->proposed_end_date;
        } elseif ($this->request_type === 'extension') {
            $data['extension_period'] = $this->extension_period;
            // Calculate proposed dates based on extension period
            $data['proposed_start_date'] = $this->currentContract->expire_date->addDay();
            $data['proposed_end_date'] = $this->currentContract->expire_date->addMonths($this->extension_period);
        } elseif ($this->request_type === 'termination') {
            $data['last_working_day'] = $this->last_working_day;
            $data['handover_notes'] = $this->handover_notes;
        }

        // Handle attachment upload
        if ($this->employee_attachment) {
            $data['employee_attachment'] = $this->employee_attachment->store('contract-requests', 'public');
        }

        ContractRequest::create($data);

        session()->flash('success', 'Your contract '.$this->request_type.' request has been submitted successfully. HR will review it shortly.');
        $this->closeModal();
    }

    public function viewRequest($id)
    {
        $this->viewingRequest = ContractRequest::with(['contract', 'reviewer'])->find($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingRequest = null;
    }

    public function cancelRequest($id)
    {
        $request = ContractRequest::where('id', $id)
            ->where('employee_id', $this->employee->id)
            ->where('status', 'pending')
            ->first();

        if ($request) {
            $request->update(['status' => 'cancelled']);
            session()->flash('success', 'Request cancelled successfully.');
        } else {
            session()->flash('error', 'Unable to cancel this request.');
        }
    }

    public function render()
    {
        $myRequests = collect();

        if ($this->employee) {
            $myRequests = ContractRequest::where('employee_id', $this->employee->id)
                ->with(['contract', 'reviewer'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('livewire.hr.contract.my-contract-request', [
            'myRequests' => $myRequests,
        ]);
    }
}
