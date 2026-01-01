<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\Employee;
use App\Models\employeeattendances;
use Livewire\Component;
use Livewire\WithPagination;

class Attendance extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Employee info
    public $employee_id;
    public $fpid;
    public $getfullname;
    public $age;
    public $gender;
    public $email;
    public $photo;
    public $editUrl;
    public $department;
    public $designation;
    public $employeeNumber;

    // Form fields
    public $attendance_id;
    public $date;
    public $clock_in;
    public $clock_out;
    public $clock_status;

    // UI state
    public $modalMode = 'create';
    public $showModal = false;
    public $confirmingDelete = null;

    // Filters
    public $search = '';
    public $filterMonth = '';
    public $filterYear = '';
    public $filterStatus = '';

    protected function rules()
    {
        return [
            'date' => ['required', 'date'],
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i', 'after:clock_in'],
            'clock_status' => ['required', 'string', 'in:Present,Absent,Late,Half Day,On Leave'],
        ];
    }

    protected $messages = [
        'date.required' => 'Date is required.',
        'clock_status.required' => 'Please select attendance status.',
        'clock_out.after' => 'Clock out time must be after clock in time.',
    ];

    public function mount($id = null)
    {
        $staff = Employee::with(['department', 'designation'])->findOrFail($id);

        $this->employee_id = $id;
        $this->fpid = $staff->fpid;
        $this->getfullname = $staff->getFullName();
        $this->age = $staff->getAgeAttribute();
        $this->gender = $staff->gender;
        $this->email = $staff->email;
        $this->photo = $staff->photo;
        $this->editUrl = route('hr.editstaff', $id);
        $this->department = $staff->department?->name;
        $this->designation = $staff->designation?->name;
        $this->employeeNumber = $staff->employee_number ?? $staff->id;

        // Default filter to current month/year
        $this->filterMonth = now()->format('m');
        $this->filterYear = now()->format('Y');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterYear()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;

        if ($mode === 'edit' && $id) {
            $attendance = employeeattendances::findOrFail($id);
            $this->attendance_id = $id;
            $this->date = $attendance->clockdate;
            $this->clock_in = $attendance->clock_in ? date('H:i', strtotime($attendance->clock_in)) : null;
            $this->clock_out = $attendance->clock_out ? date('H:i', strtotime($attendance->clock_out)) : null;
            $this->clock_status = $attendance->clock_status;
        } else {
            $this->resetForm();
            $this->date = now()->format('Y-m-d');
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['attendance_id', 'date', 'clock_in', 'clock_out', 'clock_status']);
    }

    public function save()
    {
        $this->validate();

        if (!$this->fpid) {
            session()->flash('error', 'Employee does not have a fingerprint ID assigned. Please assign one first.');
            $this->showModal = false;
            return;
        }

        $data = [
            'clockdate' => $this->date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'clock_status' => $this->clock_status,
        ];

        if ($this->modalMode === 'edit' && $this->attendance_id) {
            $attendance = employeeattendances::findOrFail($this->attendance_id);
            $attendance->update($data);
            session()->flash('success', 'Attendance record updated successfully!');
        } else {
            $data['fpuser_id'] = $this->fpid;
            employeeattendances::create($data);
            session()->flash('success', 'Attendance record added successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->confirmingDelete = $id;
    }

    public function delete()
    {
        if ($this->confirmingDelete) {
            $attendance = employeeattendances::findOrFail($this->confirmingDelete);
            $attendance->delete();
            $this->confirmingDelete = null;
            session()->flash('success', 'Attendance record deleted successfully!');
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterStatus']);
        $this->filterMonth = now()->format('m');
        $this->filterYear = now()->format('Y');
    }

    public function getAttendanceStats()
    {
        if (!$this->fpid) {
            return ['present' => 0, 'absent' => 0, 'late' => 0, 'leave' => 0, 'total' => 0];
        }

        $query = employeeattendances::where('fpuser_id', $this->fpid);

        if ($this->filterMonth && $this->filterYear) {
            $query->whereMonth('clockdate', $this->filterMonth)
                  ->whereYear('clockdate', $this->filterYear);
        }

        $records = $query->get();

        return [
            'present' => $records->where('clock_status', 'Present')->count(),
            'absent' => $records->where('clock_status', 'Absent')->count(),
            'late' => $records->where('clock_status', 'Late')->count(),
            'leave' => $records->where('clock_status', 'On Leave')->count(),
            'total' => $records->count(),
        ];
    }

    public function render()
    {
        $attendances = collect();

        if ($this->fpid) {
            $query = employeeattendances::where('fpuser_id', $this->fpid);

            if ($this->filterMonth) {
                $query->whereMonth('clockdate', $this->filterMonth);
            }

            if ($this->filterYear) {
                $query->whereYear('clockdate', $this->filterYear);
            }

            if ($this->filterStatus) {
                $query->where('clock_status', $this->filterStatus);
            }

            $attendances = $query->orderBy('clockdate', 'desc')->paginate(15);
        }

        $stats = $this->getAttendanceStats();

        return view('livewire.hr.staffs.attendance', [
            'attendances' => $attendances,
            'stats' => $stats,
        ]);
    }
}
