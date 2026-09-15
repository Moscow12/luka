<?php

namespace App\Livewire\Setup\Approval;

use App\Models\approvallevel;
use App\Models\approvalleveltodocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Documentmappings extends Component
{
    public $search = '';
    public $approval_level_document_id;
    public $modalMode = 'create';
    public $showModal = false;
    public $approval_level_id, $document_type, $document_sub_type, $is_active = true;
    public $approvallevels = [];
    public $documentTypes = [
        'Leave' => 'Leave',
        'Appraisal' => 'Appraisal',
        'Roster' => 'Roster',
        'Payroll' => 'Payroll',
        'Allowances' => 'Allowances',
        'Loan' => 'Loan',
        'Performance' => 'Performance',
        'Purchase' => 'Purchase',
    ];

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $doc = approvalleveltodocument::findOrFail($id);
            $this->approval_level_document_id = $id;
            $this->approval_level_id = $doc->approval_level_id;
            $this->document_type = $doc->document_type;
            $this->document_sub_type = $doc->document_sub_type;
            $this->is_active = $doc->is_active;
        } else {
            $this->reset(['approval_level_document_id', 'approval_level_id', 'document_type', 'document_sub_type']);
            $this->is_active = true;
        }
    }

    public function save()
    {
        $this->validate([
            'approval_level_id' => ['required', 'exists:approvallevels,id'],
            'document_type' => ['required', 'string', 'max:255'],
            'document_sub_type' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        if ($this->modalMode === 'edit' && $this->approval_level_document_id) {
            $doc = approvalleveltodocument::findOrFail($this->approval_level_document_id);
            $doc->update([
                'approval_level_id' => $this->approval_level_id,
                'document_type' => $this->document_type,
                'document_sub_type' => $this->document_sub_type,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Document approval mapping updated successfully!');
        } else {
            approvalleveltodocument::create([
                'approval_level_id' => $this->approval_level_id,
                'document_type' => $this->document_type,
                'document_sub_type' => $this->document_sub_type,
                'is_active' => $this->is_active,
                'added_by' => Auth::user()->id
            ]);
            session()->flash('success', 'Document approval mapping added successfully!');
        }

        $this->showModal = false;
        $this->reset(['approval_level_document_id', 'approval_level_id', 'document_type', 'document_sub_type']);
    }

    public function update()
    {
        $this->save();
    }

    public function delete($uuid)
    {
        $doc = approvalleveltodocument::findOrFail($uuid);
        $doc->delete();
        session()->flash('success', 'Document approval mapping deleted successfully!');
    }

    public function mount()
    {
        $this->approvallevels = approvallevel::where('is_active', true)->orderBy('level_order')->get();
    }

    public function render()
    {
        $documents = approvalleveltodocument::with('approval_level')
            ->where('document_type', 'like', '%' . $this->search . '%')
            ->paginate(10);
        return view('livewire.setup.approval.documentmappings', ['documents' => $documents]);
    }
}
