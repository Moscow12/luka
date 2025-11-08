<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\{Employeeotherattachments, Employee};
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Otherdocuments extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $otherdocument_id,  $type, $description, $attachment, $added_by;
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl, $photo;
    public $employee_id, $otherdocuments=[];

    use WithFileUploads;
    public function mount($id=null)
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
        $this->validate([
            'type' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'attachment' => 'required',
        ]);
        //upload attachment
        if ($this->attachment) {
            // Store file in "attachments" folder inside /storage/app/public/
            $path = $this->attachment->store('attachments', 'public');
            $this->attachment = $path;
        }

        if ($this->modalMode === 'create') {
            Employeeotherattachments::create([
                'type' => $this->type,
                'description' => $this->description,
                'attachment' => $this->attachment,
                'employee_id' => $this->employee_id,
                'added_by' => Auth::user()->id
            ]);
        } elseif ($this->modalMode === 'edit' && $this->otherdocument_id) {
            $otherdocument = Employeeotherattachments::findOrFail($this->otherdocument_id);
            $otherdocument->update([
                'type' => $this->type,
                'description' => $this->description,
                'attachment' => $this->attachment,
            ]);
        }

        $this->listdata();
        $this->showModal = false;
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
