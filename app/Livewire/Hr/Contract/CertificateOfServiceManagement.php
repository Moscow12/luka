<?php

namespace App\Livewire\Hr\Contract;

use App\Models\CertificateOfService;
use App\Models\ContractRequest;
use App\Models\Employee;
use App\Models\Employeecontracts;
use App\Models\TerminationReason;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CertificateOfServiceManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $filterStatus = '';

    public $filterType = '';

    public $search = '';

    // Certificate form
    public $showModal = false;

    public $editingId = null;

    public $employee_id = '';

    public $contract_id = '';

    public $contract_request_id = '';

    public $separation_type = 'end_of_contract';

    public $termination_reason_id = '';

    public $service_start_date = '';

    public $service_end_date = '';

    public $position_held = '';

    public $department = '';

    public $duties_performed = '';

    public $additional_remarks = '';

    public $declaration_text = '';

    public $workstation_id = '';

    // For viewing/printing
    public $viewingCertificate = null;

    public $showViewModal = false;

    public $showPrintModal = false;

    // Data for dropdowns
    public $employees = [];

    public $contracts = [];

    public $terminationReasons = [];

    public $workstations = [];

    // Statistics
    public $pendingCount = 0;

    public $approvedCount = 0;

    public $printedCount = 0;

    public function mount()
    {
        $this->loadStatistics();
        $this->loadDropdownData();
        $this->declaration_text = CertificateOfService::getDefaultDeclarationText();
    }

    protected function loadStatistics()
    {
        $this->pendingCount = CertificateOfService::where('status', 'pending_approval')->count();
        $this->approvedCount = CertificateOfService::where('status', 'approved')->count();
        $this->printedCount = CertificateOfService::where('status', 'printed')->count();
    }

    protected function loadDropdownData()
    {
        $this->terminationReasons = TerminationReason::active()->orderBy('name')->get();
        $this->workstations = workstations::all();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedEmployeeId($value)
    {
        if ($value) {
            $this->contracts = Employeecontracts::where('employee_id', $value)
                ->orderBy('start_date', 'desc')
                ->get();

            // Auto-fill employee details
            $employee = Employee::with(['department', 'designation'])->find($value);
            if ($employee) {
                $this->department = $employee->department?->name ?? '';
                $this->position_held = $employee->designation?->name ?? '';
            }
        } else {
            $this->contracts = [];
        }
        $this->contract_id = '';
    }

    public function updatedContractId($value)
    {
        if ($value) {
            $contract = Employeecontracts::find($value);
            if ($contract) {
                $this->service_start_date = $contract->start_date->format('Y-m-d');
                $this->service_end_date = $contract->expire_date->format('Y-m-d');
                $this->position_held = $contract->position?->name ?? $this->position_held;
            }
        }
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        $this->loadDropdownData();

        // Load terminated/end of contract employees
        $this->employees = Employee::whereIn('status', ['Terminated', 'Inactive'])
            ->orWhereHas('contracts', function ($q) {
                $q->where('status', 'terminated')
                    ->orWhere('expire_date', '<=', now());
            })
            ->orderBy('first_name')
            ->get();

        if ($id) {
            $certificate = CertificateOfService::with(['employee', 'contract'])->find($id);
            if ($certificate && $certificate->canBeEdited()) {
                $this->editingId = $id;
                $this->employee_id = $certificate->employee_id;

                // Load contracts for this employee
                $this->contracts = Employeecontracts::where('employee_id', $certificate->employee_id)
                    ->orderBy('start_date', 'desc')
                    ->get();

                $this->contract_id = $certificate->contract_id;
                $this->contract_request_id = $certificate->contract_request_id;
                $this->separation_type = $certificate->separation_type;
                $this->termination_reason_id = $certificate->termination_reason_id;
                $this->service_start_date = $certificate->service_start_date->format('Y-m-d');
                $this->service_end_date = $certificate->service_end_date->format('Y-m-d');
                $this->position_held = $certificate->position_held;
                $this->department = $certificate->department;
                $this->duties_performed = $certificate->duties_performed;
                $this->additional_remarks = $certificate->additional_remarks;
                $this->declaration_text = $certificate->declaration_text;
                $this->workstation_id = $certificate->workstation_id;
            }
        }

        $this->showModal = true;
    }

    public function openModalFromRequest($requestId)
    {
        $this->resetForm();
        $this->loadDropdownData();

        $request = ContractRequest::with(['employee', 'contract', 'terminationReason'])->find($requestId);

        if ($request && $request->status === 'approved' && $request->request_type === 'termination') {
            $this->employee_id = $request->employee_id;
            $this->contract_id = $request->contract_id;
            $this->contract_request_id = $request->id;

            // Load contracts for this employee
            $this->contracts = Employeecontracts::where('employee_id', $request->employee_id)
                ->orderBy('start_date', 'desc')
                ->get();

            $this->separation_type = 'termination';
            $this->termination_reason_id = $request->termination_reason_id;

            if ($request->contract) {
                $this->service_start_date = $request->contract->start_date->format('Y-m-d');
                $this->service_end_date = $request->last_working_day?->format('Y-m-d') ?? $request->contract->expire_date->format('Y-m-d');
                $this->position_held = $request->contract->position?->name ?? '';
            }

            if ($request->employee) {
                $this->department = $request->employee->department?->name ?? '';
                // Also add all terminated employees to the dropdown
                $this->employees = Employee::whereIn('status', ['Terminated', 'Inactive'])
                    ->orWhere('id', $request->employee_id)
                    ->orderBy('first_name')
                    ->get();
            }

            $this->declaration_text = CertificateOfService::getDefaultDeclarationText();
        }

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
            'employee_id',
            'contract_id',
            'contract_request_id',
            'separation_type',
            'termination_reason_id',
            'service_start_date',
            'service_end_date',
            'position_held',
            'department',
            'duties_performed',
            'additional_remarks',
            'workstation_id',
        ]);
        $this->separation_type = 'end_of_contract';
        $this->declaration_text = CertificateOfService::getDefaultDeclarationText();
        $this->contracts = [];
        $this->resetValidation();
    }

    protected function rules()
    {
        return [
            'employee_id' => 'required|uuid|exists:employees,id',
            'contract_id' => 'required|uuid|exists:employeecontracts,id',
            'separation_type' => 'required|in:termination,end_of_contract,resignation,retirement,other',
            'termination_reason_id' => 'nullable|uuid|exists:termination_reasons,id',
            'service_start_date' => 'required|date',
            'service_end_date' => 'required|date|after_or_equal:service_start_date',
            'position_held' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'duties_performed' => 'nullable|string|max:2000',
            'additional_remarks' => 'nullable|string|max:1000',
            'declaration_text' => 'required|string|max:2000',
            'workstation_id' => 'nullable|uuid|exists:workstations,id',
        ];
    }

    public function saveDraft()
    {
        $this->validate();
        $this->saveOrUpdate('draft');
        session()->flash('success', 'Certificate saved as draft.');
        $this->closeModal();
    }

    public function submitForApproval()
    {
        $this->validate();
        $this->saveOrUpdate('pending_approval');
        session()->flash('success', 'Certificate submitted for approval.');
        $this->closeModal();
    }

    protected function saveOrUpdate($status)
    {
        $data = [
            'employee_id' => $this->employee_id,
            'contract_id' => $this->contract_id,
            'contract_request_id' => $this->contract_request_id ?: null,
            'separation_type' => $this->separation_type,
            'termination_reason_id' => $this->termination_reason_id ?: null,
            'service_start_date' => $this->service_start_date,
            'service_end_date' => $this->service_end_date,
            'position_held' => $this->position_held,
            'department' => $this->department,
            'duties_performed' => $this->duties_performed,
            'additional_remarks' => $this->additional_remarks,
            'declaration_text' => $this->declaration_text,
            'workstation_id' => $this->workstation_id ?: null,
            'status' => $status,
        ];

        if ($this->editingId) {
            $certificate = CertificateOfService::find($this->editingId);
            $certificate->update($data);
        } else {
            $data['prepared_by'] = Auth::id();
            CertificateOfService::create($data);
        }

        $this->loadStatistics();
    }

    public function viewCertificate($id)
    {
        $this->viewingCertificate = CertificateOfService::with([
            'employee',
            'contract',
            'terminationReason',
            'preparedBy',
            'approvedBy',
            'workstation',
        ])->find($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingCertificate = null;
    }

    public function approveCertificate($id)
    {
        $certificate = CertificateOfService::find($id);

        if ($certificate && $certificate->canBeApproved()) {
            $certificate->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            session()->flash('success', 'Certificate approved successfully.');
            $this->loadStatistics();
            $this->closeViewModal();
        }
    }

    public function rejectCertificate($id, $comments = '')
    {
        $certificate = CertificateOfService::find($id);

        if ($certificate && $certificate->canBeApproved()) {
            $certificate->update([
                'status' => 'draft',
                'approval_comments' => $comments,
            ]);
            session()->flash('success', 'Certificate returned for revision.');
            $this->loadStatistics();
            $this->closeViewModal();
        }
    }

    public function openPrintModal($id)
    {
        $this->viewingCertificate = CertificateOfService::with([
            'employee',
            'contract',
            'terminationReason',
            'preparedBy',
            'approvedBy',
            'workstation',
        ])->find($id);

        if ($this->viewingCertificate && $this->viewingCertificate->canBePrinted()) {
            $this->showPrintModal = true;
        }
    }

    public function closePrintModal()
    {
        $this->showPrintModal = false;
        $this->viewingCertificate = null;
    }

    public function markAsPrinted($id)
    {
        $certificate = CertificateOfService::find($id);

        if ($certificate && $certificate->canBePrinted()) {
            $certificate->update([
                'status' => 'printed',
                'printed_at' => now(),
                'print_count' => $certificate->print_count + 1,
            ]);
            $this->loadStatistics();
        }
    }

    public function cancelCertificate($id)
    {
        $certificate = CertificateOfService::find($id);

        if ($certificate && in_array($certificate->status, ['draft', 'pending_approval'])) {
            $certificate->update(['status' => 'cancelled']);
            session()->flash('success', 'Certificate cancelled.');
            $this->loadStatistics();
        }
    }

    public function render()
    {
        $certificates = CertificateOfService::with(['employee', 'contract', 'preparedBy', 'approvedBy'])
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn ($q) => $q->where('separation_type', $this->filterType))
            ->when($this->search, function ($q) {
                $q->whereHas('employee', function ($query) {
                    $query->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('middle_name', 'like', '%'.$this->search.'%');
                })
                    ->orWhere('certificate_number', 'like', '%'.$this->search.'%');
            })
            ->orderByRaw("FIELD(status, 'pending_approval', 'approved', 'draft', 'printed', 'cancelled')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get pending termination requests that don't have certificates yet
        $pendingTerminations = ContractRequest::with(['employee', 'contract'])
            ->where('request_type', 'termination')
            ->where('status', 'approved')
            ->whereDoesntHave('certificate')
            ->get();

        return view('livewire.hr.contract.certificate-of-service-management', [
            'certificates' => $certificates,
            'pendingTerminations' => $pendingTerminations,
        ]);
    }
}
