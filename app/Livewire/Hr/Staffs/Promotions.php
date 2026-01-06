<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\departments;
use App\Models\Employee;
use App\Models\Employeepromotions;
use App\Models\Jobtitle;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Promotions extends Component
{
    public $search = '';

    public $modalMode = 'create';

    public $showModal = false;

    public $employee_id;

    public $promotions = [];

    public $workstations = [];

    public $departments = [];

    public $titles = [];

    public $promotion_id;

    public $title_id;

    public $workstation_id;

    public $start_date;

    public $department_id;

    public $attachment;

    public $comments;

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
        $this->workstations = workstations::all();
        $this->departments = departments::all();
        $this->titles = Jobtitle::all();
        $this->listdata();
    }

    public function listdata()
    {
        $this->promotions = Employeepromotions::where('employee_id', $this->employee_id)->get();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            // Load promotion data for editing if needed
            $promotion = Employeepromotions::findOrFail($id);
            $this->promotion_id = $id;
            $this->title_id = $promotion->title_id;
            $this->workstation_id = $promotion->workstation_id;
            $this->start_date = $promotion->start_date;
            $this->department_id = $promotion->department_id;
            $this->attachment = $promotion->attachment;
            $this->comments = $promotion->comments;

        } else {
            // Reset promotion fields for creation if needed
            $this->reset(['title_id', 'workstation_id', 'start_date', 'department_id', 'attachment', 'comments']);
        }
    }

    public function save()
    {
        $rules = [
            'title_id' => ['required', 'string', 'max:255'],
            'workstation_id' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'comments' => ['required', 'string'],
        ];

        // Attachment is required only for new promotions, optional when editing
        if ($this->modalMode === 'create') {
            $rules['attachment'] = ['required', 'file', 'max:10240'];
        } else {
            $rules['attachment'] = ['nullable', 'file', 'max:10240'];
        }

        $this->validate($rules, [
            'title_id.required' => 'Please select a job title.',
            'workstation_id.required' => 'Please select a workstation.',
            'department_id.required' => 'Please select a department.',
            'start_date.required' => 'Please enter the promotion date.',
            'comments.required' => 'Please add comments about this promotion.',
            'attachment.required' => 'Please upload the promotion letter or supporting document.',
            'attachment.file' => 'The attachment must be a valid file.',
            'attachment.max' => 'The attachment file size must not exceed 10MB.',
        ]);

        // Upload attachment (only if a new file was uploaded)
        if ($this->attachment && is_object($this->attachment) && method_exists($this->attachment, 'store')) {
            $path = $this->attachment->store('attachments', 'public');
            $this->attachment = $path;
        }

        if ($this->modalMode === 'edit' && $this->promotion_id) {
            // Update promotion data if needed
            $promotion = Employeepromotions::findOrFail($this->promotion_id);
            $promotion->update(['title_id' => $this->title_id, 'workstation_id' => $this->workstation_id, 'start_date' => $this->start_date, 'department_id' => $this->department_id, 'comments' => $this->comments, 'attachment' => $this->attachment]);
            $this->listdata();
            session()->flash('success', 'Promotion updated successfully!');
        } else {
            // Create new promotion
            Employeepromotions::create([
                'title_id' => $this->title_id,
                'workstation_id' => $this->workstation_id,
                'start_date' => $this->start_date,
                'department_id' => $this->department_id,
                'attachment' => $this->attachment,
                'comments' => $this->comments,
                'employee_id' => $this->employee_id,
                'added_by' => Auth::user()->id,

            ]);
            $this->listdata();
            session()->flash('success', 'Promotion added successfully!');
        }
        $this->showModal = false;
        $this->reset(['title_id', 'workstation_id', 'start_date', 'department_id', 'attachment', 'comments']);
    }

    public function delete($uuid)
    {
        $promotion = Employeepromotions::findOrFail($uuid);
        $promotion->delete();
        $this->listdata();
        session()->flash('success', 'Promotion deleted successfully!');
    }

    public function render()
    {
        return view('livewire.hr.staffs.promotions');
    }
}
