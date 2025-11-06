<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\Employeedisplineissue;
use App\Models\violations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Disciplinary extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    
    public $employee_id, $displineissues=[], $violations=[],  $displine_id, $violation_id, $violation_date, $department_id, $attachment, $notes;
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl;

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
            $displine = Employeedisplineissue::findOrFail($id);
            $this->displine_id = $id;
            $this->violation_id = $displine->violation_id;
            $this->violation_date = $displine->violation_date;
            $this->attachment = $displine->attachment;
            $this->notes = $displine->notes;
                
        } else {
            // Reset displine fields for creation if needed
            $this->reset(['violation_id','violation_date',  'attachment', 'notes']);
        }
    }
    public function save()
    {
        $this->validate([
            'violation_id' => ['required', 'string', 'max:255'],
            'violation_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'], // Max 10MB

        ]);

        if ($this->modalMode === 'edit' && $this->displine_id) {
            // Update displine data if needed
            $displine = Employeedisplineissue::findOrFail($this->displine_id);
            $displine->update(['violation_id' => $this->violation_id, 'violation_date' => $this->violation_date, 'notes' => $this->notes, 'attachment' => $this->attachment]);
            $this->listdata();
            session()->flash('success', 'Displine updated successfully!');
        } else {
            // Create new displine
            Employeedisplineissue::create([
                'violation_id' => $this->violation_id,
                'violation_date' => $this->violation_date,
                'notes' => $this->notes,
                'attachment' => $this->attachment,
                'employee_id' => $this->employee_id,
                'added_by' => Auth::user()->id

            ]);
            $this->listdata();
            session()->flash('success', 'Displine added successfully!');
        }
        $this->showModal = false;
        $this->reset(['violation_id','violation_date',  'attachment', 'notes']);
    }
    public function delete($uuid)
    {
        $displine = Employeedisplineissue::findOrFail($uuid);
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
