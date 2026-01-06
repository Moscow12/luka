<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\Employeeotherattachments;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Otherdocuments extends Component
{
    public $search = '';

    public $modalMode = 'create';

    public $showModal = false;

    public $otherdocument_id;

    public $type;

    public $description;

    public $attachment;

    public $added_by;

    public $first_name;

    public $middle_name;

    public $last_name;

    public $gender;

    public $getfullname;

    public $age;

    public $email;

    public $editUrl;

    public $photo;

    public $employee_id;

    public $otherdocuments = [];

    use WithFileUploads;

    public function mount($id = null)
    {
        $staff = Employee::findOrFail($id);
        $this->employee_id = $id;

        $this->first_name = $staff->first_name;
        $this->middle_name = $staff->middle_name;
        $this->last_name = $staff->last_name;
        $this->getfullname = $staff->getFullName();
        $this->age = $staff->getAgeAttribute();
        $this->gender = $staff->gender;
        $this->email = $staff->email;
        $this->photo = $staff->photo;
        $this->editUrl = route('hr.editstaff', $id);
        $this->listdata();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            // Load otherdocument data for editing if needed
            $otherdocument = Employeeotherattachments::findOrFail($id);
            $this->otherdocument_id = $id;
            $this->type = $otherdocument->type;
            $this->description = $otherdocument->description;
            $this->attachment = $otherdocument->attachment;

        } else {
            // Reset otherdocument fields for creation if needed
            $this->reset(['type', 'description', 'attachment']);
        }
    }

    public function save()
    {
        $rules = [
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:500',
        ];

        // Attachment is required only for new documents, optional when editing
        if ($this->modalMode === 'create') {
            $rules['attachment'] = ['required', 'file', 'max:10240'];
        } else {
            $rules['attachment'] = ['nullable', 'file', 'max:10240'];
        }

        $this->validate($rules, [
            'type.required' => 'Please enter the document type.',
            'description.required' => 'Please enter a description for this document.',
            'attachment.required' => 'Please upload a document.',
            'attachment.file' => 'The attachment must be a valid file.',
            'attachment.max' => 'The attachment file size must not exceed 10MB.',
        ]);

        // Upload attachment (only if a new file was uploaded)
        if ($this->attachment && is_object($this->attachment) && method_exists($this->attachment, 'store')) {
            $path = $this->attachment->store('attachments', 'public');
            $this->attachment = $path;
        }

        if ($this->modalMode === 'create') {
            Employeeotherattachments::create([
                'type' => $this->type,
                'description' => $this->description,
                'attachment' => $this->attachment,
                'employee_id' => $this->employee_id,
                'added_by' => Auth::user()->id,
            ]);
            session()->flash('success', 'Document added successfully!');
        } elseif ($this->modalMode === 'edit' && $this->otherdocument_id) {
            $otherdocument = Employeeotherattachments::findOrFail($this->otherdocument_id);
            $otherdocument->update([
                'type' => $this->type,
                'description' => $this->description,
                'attachment' => $this->attachment,
            ]);
            session()->flash('success', 'Document updated successfully!');
        }

        $this->listdata();
        $this->showModal = false;
        $this->reset(['type', 'description', 'attachment']);
    }

    public function listdata()
    {
        $this->otherdocuments = Employeeotherattachments::where('employee_id', $this->employee_id)->get();
    }

    public function delete($uuid)
    {
        $otherdocument = Employeeotherattachments::findOrFail($uuid);
        $otherdocument->delete();
        $this->listdata();
    }

    public function render()
    {
        return view('livewire.hr.staffs.otherdocuments');
    }
}
