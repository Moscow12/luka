<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\Employeeotherattachments;
use App\Services\HrDocuments\HrDocumentStore;
use App\Services\HrDocuments\HrDocumentType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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

    #[Locked]
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
            $otherdocument = Employeeotherattachments::where('employee_id', $this->employee_id)->findOrFail($id);
            $this->otherdocument_id = $id;
            $this->type = $otherdocument->type;
            $this->description = $otherdocument->description;
            $this->attachment = null; // upload slot only; the stored file stays on the record

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
            $rules['attachment'] = HrDocumentStore::rules(HrDocumentType::OtherDocument, required: true);
        } else {
            $rules['attachment'] = HrDocumentStore::rules(HrDocumentType::OtherDocument);
        }

        $this->validate($rules, [
            'type.required' => 'Please enter the document type.',
            'description.required' => 'Please enter a description for this document.',
            'attachment.required' => 'Please upload a document.',
            'attachment.file' => 'The attachment must be a valid file.',
            'attachment.max' => 'The attachment file size must not exceed 10MB.',
            'attachment.extensions' => 'Only '.HrDocumentStore::acceptedLabel(HrDocumentType::OtherDocument).' files are accepted.',
            'attachment.mimes' => 'Only '.HrDocumentStore::acceptedLabel(HrDocumentType::OtherDocument).' files are accepted.',
            'attachment.mimetypes' => 'Only '.HrDocumentStore::acceptedLabel(HrDocumentType::OtherDocument).' files are accepted.',
        ]);

        // New uploads go to encrypted private storage; the path never comes from the client

        $documents = app(HrDocumentStore::class);

        $upload = $this->attachment instanceof TemporaryUploadedFile ? $this->attachment : null;

        if ($this->modalMode === 'create') {
            Employeeotherattachments::create([
                'type' => $this->type,
                'description' => $this->description,
                'attachment' => $upload ? $documents->store(HrDocumentType::OtherDocument, $upload) : null,
                'employee_id' => $this->employee_id,
                'added_by' => Auth::user()->id,
            ]);
            session()->flash('success', 'Document added successfully!');
        } elseif ($this->modalMode === 'edit' && $this->otherdocument_id) {
            $otherdocument = Employeeotherattachments::where('employee_id', $this->employee_id)->findOrFail($this->otherdocument_id);
            $otherdocument->update([
                'type' => $this->type,
                'description' => $this->description,
                'attachment' => $upload ? $documents->replace(HrDocumentType::OtherDocument, $otherdocument->attachment, $upload) : $otherdocument->attachment,
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
        $otherdocument = Employeeotherattachments::where('employee_id', $this->employee_id)->findOrFail($uuid);
        app(HrDocumentStore::class)->delete(HrDocumentType::OtherDocument, $otherdocument->attachment);
        $otherdocument->delete();
        $this->listdata();
    }

    public function render()
    {
        return view('livewire.hr.staffs.otherdocuments');
    }
}
