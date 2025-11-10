<?php

namespace App\Livewire\Contracts;

use App\Models\contract_approvals;
use App\Models\contract_deliverables;
use App\Models\contract_documents;
use App\Models\contract_renewals;
use App\Models\ContractParty;
use App\Models\contracts;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContractDetails extends Component
{
    use WithFileUploads;

    public $contract;

    public $activeTab = 'overview';

    // Document upload
    public $document;

    public $documentName;

    public $documentNumber;

    public $documentDescription;

    // Deliverable
    public $deliverableName;

    public $deliverableType;

    public $deliverableDescription;

    public $deliverableKpi;

    public $deliverableDueDate;

    // Renewal
    public $newEndDate;

    public $newValue;

    public $renewalRemarks;

    // Approval action
    public $approvalId;

    public $approvalComments;

    // Party
    public $partyType;

    public $partyName;

    public $contactPerson;

    public $partyEmail;

    public $partyPhone;

    public $partyAddress;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($contractId)
    {
        $this->contract = contracts::with([
            'department',
            'vendor',
            'addedBy',
            'parties.addedBy',
            'documents.addedBy',
            'deliverables.addedBy',
            'renewals.addedBy',
            'approvals.approver',
        ])->findOrFail($contractId);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function uploadDocument()
    {
        $this->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'documentName' => 'required|string|max:255',
        ]);

        try {
            $path = $this->document->store('contracts/documents', 'public');

            contract_documents::create([
                'contract_id' => $this->contract->id,
                'document_name' => $this->documentName,
                'document_number' => $this->documentNumber,
                'file_path' => $path,
                'description' => $this->documentDescription,
                'version' => '1.0',
                'status' => 'active',
                'added_by' => Auth::id(),
            ]);

            $this->reset(['document', 'documentName', 'documentNumber', 'documentDescription']);
            $this->contract->refresh();

            $this->dispatch('toaster', ['type' => 'success', 'message' => 'Document uploaded successfully']);
        } catch (\Exception) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error uploading document']);
        }
    }

    public function addDeliverable()
    {
        $this->validate([
            'deliverableName' => 'required|string|max:255',
            'deliverableType' => 'required|string|max:255',
            'deliverableDueDate' => 'nullable|date',
        ]);

        try {
            contract_deliverables::create([
                'contract_id' => $this->contract->id,
                'deliverable_name' => $this->deliverableName,
                'deliverable_type' => $this->deliverableType,
                'description' => $this->deliverableDescription,
                'deliverable' => $this->deliverableName,
                'kpi' => $this->deliverableKpi,
                'due_date' => $this->deliverableDueDate,
                'status' => 'pending',
                'added_by' => Auth::id(),
            ]);

            $this->reset(['deliverableName', 'deliverableType', 'deliverableDescription', 'deliverableKpi', 'deliverableDueDate']);
            $this->contract->refresh();

            $this->dispatch('toaster', ['type' => 'success', 'message' => 'Deliverable added successfully']);
        } catch (\Exception) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error adding deliverable']);
        }
    }

    public function renewContract()
    {
        $this->validate([
            'newEndDate' => 'required|date|after:'.$this->contract->end_date,
        ]);

        try {
            contract_renewals::create([
                'contract_id' => $this->contract->id,
                'old_end_date' => $this->contract->end_date,
                'new_end_date' => $this->newEndDate,
                'new_value' => $this->newValue,
                'remarks' => $this->renewalRemarks,
                'added_by' => Auth::id(),
            ]);

            $this->contract->update(['end_date' => $this->newEndDate]);
            if ($this->newValue) {
                $this->contract->update(['contract_value' => $this->newValue]);
            }

            $this->reset(['newEndDate', 'newValue', 'renewalRemarks']);
            $this->contract->refresh();

            $this->dispatch('toaster', ['type' => 'success', 'message' => 'Contract renewed successfully']);
        } catch (\Exception) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error renewing contract']);
        }
    }

    public function approveContract($approvalId)
    {
        try {
            $approval = contract_approvals::findOrFail($approvalId);
            $approval->update([
                'status' => 'approved',
                'comments' => $this->approvalComments,
            ]);

            // Check if all approvals are complete
            $allApproved = $this->contract->approvals()->where('status', '!=', 'approved')->count() === 0;
            if ($allApproved) {
                $this->contract->update(['status' => 'active']);
            }

            $this->reset(['approvalComments']);
            $this->contract->refresh();

            $this->dispatch('toaster', ['type' => 'success', 'message' => 'Contract approved successfully']);
        } catch (\Exception) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error approving contract']);
        }
    }

    public function rejectContract($approvalId)
    {
        try {
            $approval = contract_approvals::findOrFail($approvalId);
            $approval->update([
                'status' => 'rejected',
                'comments' => $this->approvalComments,
            ]);

            $this->contract->update(['status' => 'draft']);

            $this->reset(['approvalComments']);
            $this->contract->refresh();

            $this->dispatch('toaster', ['type' => 'warning', 'message' => 'Contract approval rejected']);
        } catch (\Exception) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error rejecting contract']);
        }
    }

    public function addParty()
    {
        $this->validate([
            'partyType' => 'required|string',
            'partyName' => 'required|string|max:255',
            'partyEmail' => 'nullable|email',
        ]);

        try {
            ContractParty::create([
                'contract_id' => $this->contract->id,
                'party_type' => $this->partyType,
                'party_name' => $this->partyName,
                'contact_person' => $this->contactPerson,
                'email' => $this->partyEmail,
                'phone' => $this->partyPhone,
                'address' => $this->partyAddress,
                'status' => 'active',
                'added_by' => Auth::id(),
            ]);

            $this->reset(['partyType', 'partyName', 'contactPerson', 'partyEmail', 'partyPhone', 'partyAddress']);
            $this->contract->refresh();

            $this->dispatch('toaster', ['type' => 'success', 'message' => 'Party added successfully']);
        } catch (\Exception) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error adding party']);
        }
    }

    public function render()
    {
        return view('livewire.contracts.contract-details');
    }
}
