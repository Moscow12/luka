<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\Employeequalifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Qualifications extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $education_level, $institution, $start_date, $end_date, $attachment, $comments;
    public $qualification_id;
    
    public $employee_id, $qualifications;
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl, $photo;

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
            // Load qualification data for editing if needed
            $qualification = Employeequalifications::findOrFail($id);
            $this->qualification_id = $id;
            $this->education_level = $qualification->education_level;
            $this->institution = $qualification->institution;
            $this->start_date = $qualification->start_date;
            $this->end_date = $qualification->end_date;
            $this->attachment = $qualification->attachment;
            $this->comments = $qualification->comments;
                
        } else {
            // Reset qualification fields for creation if needed
            $this->reset(['education_level', 'institution', 'start_date', 'end_date', 'attachment', 'comments']);
        }
    }

     public function save()
    {
        $this->validate([
            'education_level' => ['required', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'string', 'max:255'],
            'end_date' => ['required', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:10240'], // Max 10MB
            'comments' => ['nullable', 'string'],

        ]);
        //upload attachment
        if ($this->attachment) {
            // Store file in "attachments" folder inside /storage/app/public/
            $path = $this->attachment->store('attachments', 'public');
            $this->attachment = $path;
        }
        if ($this->modalMode === 'edit' && $this->qualification_id) {
            // Update qualification data if needed
            $qualification = Employeequalifications::findOrFail($this->qualification_id);
            $qualification->update(['education_level' => $this->education_level, 'institution' => $this->institution, 'start_date' => $this->start_date, 'end_date' => $this->end_date, 'attachment' => $this->attachment, 'comments' => $this->comments]);
            $this->listdata();
            session()->flash('success', 'Qualification updated successfully!');
        } else {
            // Create new qualification
            Employeequalifications::create([
                'education_level' => $this->education_level,
                'institution' => $this->institution,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'attachment' => $this->attachment,
                'comments' => $this->comments,
                'employee_id' => $this->employee_id,
                'added_by' => Auth::user()->id

            ]);
            session()->flash('success', 'Qualification added successfully!');
        }
        $this->showModal = false;
        $this->listdata();
        $this->reset(['education_level', 'institution', 'start_date', 'end_date', 'attachment', 'comments']);
    }
    public function delete($uuid)
    {
        $qualification = Employeequalifications::findOrFail($uuid);
        $qualification->delete();
        $this->listdata();
        session()->flash('success', 'Qualification deleted successfully!');
    }


    public function listdata()
    {
        $this->qualifications = Employeequalifications::where('employee_id', $this->employee_id)->get();
    }

    public function render()
    {
        return view('livewire.hr.staffs.qualifications');
    }
}
