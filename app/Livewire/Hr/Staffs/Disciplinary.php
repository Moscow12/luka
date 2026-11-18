<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\Employeedisplineissue;
use App\Models\violations;
use App\Services\HrDocuments\HrDocumentStore;
use App\Services\HrDocuments\HrDocumentType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Disciplinary extends Component
{
    public $search = '';

    public $modalMode = 'create';

    public $showModal = false;

    #[Locked]
    public $employee_id;

    public $displineissues = [];

    public $violations = [];

    public $displine_id;

    public $violation_id;

    public $violation_date;

    public $department_id;

    public $attachment;

    public $notes;

    public $first_name;

    public $middle_name;

    public $last_name;

    public $gender;

    public $getfullname;

    public $age;

    public $email;

    public $editUrl;

    public $photo;

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
        $this->violations = violations::all();
        $this->listdata();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            // Load displine data for editing if needed
            $displine = Employeedisplineissue::where('employee_id', $this->employee_id)->findOrFail($id);
            $this->displine_id = $id;
            $this->violation_id = $displine->violation_id;
            $this->violation_date = $displine->violation_date;
            $this->attachment = null; // upload slot only; the stored file stays on the record
            $this->notes = $displine->notes;

        } else {
            // Reset displine fields for creation if needed
            $this->reset(['violation_id', 'violation_date',  'attachment', 'notes']);
        }
    }

    public function save()
    {
        $rules = [
            'violation_id' => ['required', 'string', 'max:255'],
            'violation_date' => ['required', 'date'],
            'notes' => ['required', 'string'],
        ];

        // Attachment is required only for new records, optional when editing
        if ($this->modalMode === 'create') {
            $rules['attachment'] = HrDocumentStore::rules(HrDocumentType::Disciplinary, required: true);
        } else {
            $rules['attachment'] = HrDocumentStore::rules(HrDocumentType::Disciplinary);
        }

        $this->validate($rules, [
            'violation_id.required' => 'Please select a violation type.',
            'violation_date.required' => 'Please enter the violation date.',
            'notes.required' => 'Please add notes about this disciplinary issue.',
            'attachment.required' => 'Please upload supporting documentation.',
            'attachment.file' => 'The attachment must be a valid file.',
            'attachment.max' => 'The attachment file size must not exceed 10MB.',
            'attachment.extensions' => 'Only '.HrDocumentStore::acceptedLabel(HrDocumentType::Disciplinary).' files are accepted.',
            'attachment.mimes' => 'Only '.HrDocumentStore::acceptedLabel(HrDocumentType::Disciplinary).' files are accepted.',
            'attachment.mimetypes' => 'Only '.HrDocumentStore::acceptedLabel(HrDocumentType::Disciplinary).' files are accepted.',
        ]);

        // New uploads go to encrypted private storage; the path never comes from the client

        $documents = app(HrDocumentStore::class);

        $upload = $this->attachment instanceof TemporaryUploadedFile ? $this->attachment : null;

        if ($this->modalMode === 'edit' && $this->displine_id) {
            // Update displine data if needed
            $displine = Employeedisplineissue::where('employee_id', $this->employee_id)->findOrFail($this->displine_id);
            $displine->update(['violation_id' => $this->violation_id, 'violation_date' => $this->violation_date, 'notes' => $this->notes, 'attachment' => $upload ? $documents->replace(HrDocumentType::Disciplinary, $displine->attachment, $upload) : $displine->attachment]);
            $this->listdata();
            session()->flash('success', 'Disciplinary record updated successfully!');
        } else {
            // Create new displine
            Employeedisplineissue::create([
                'violation_id' => $this->violation_id,
                'violation_date' => $this->violation_date,
                'notes' => $this->notes,
                'attachment' => $upload ? $documents->store(HrDocumentType::Disciplinary, $upload) : null,
                'employee_id' => $this->employee_id,
                'added_by' => Auth::user()->id,

            ]);
            $this->listdata();
            session()->flash('success', 'Disciplinary record added successfully!');
        }
        $this->showModal = false;
        $this->reset(['violation_id', 'violation_date',  'attachment', 'notes']);
    }

    public function delete($uuid)
    {
        $displine = Employeedisplineissue::where('employee_id', $this->employee_id)->findOrFail($uuid);
        app(HrDocumentStore::class)->delete(HrDocumentType::Disciplinary, $displine->attachment);
        $displine->delete();
        $this->listdata();
        session()->flash('success', 'Displine deleted successfully!');
    }

    public function listdata()
    {
        $this->displineissues = Employeedisplineissue::where('employee_id', $this->employee_id)->get();
    }

    public function render()
    {
        return view('livewire.hr.staffs.disciplinary');
    }
}
