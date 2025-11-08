<?php

namespace App\Livewire\Hr\Leave;

use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\Leaves;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Leavebalance extends Component
{
    public $employee;
    public $employee_id;
    public $hasEmployeeRecord = false;
    public $leaveBalances = [];

    public function mount()
    {
        // Check if logged-in user is connected to an employee record
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
            $this->employee_id = $this->employee->id;
            $this->calculateLeaveBalances();
        }
    }

    public function calculateLeaveBalances()
    {
        if (!$this->hasEmployeeRecord) return;

        // Get all active leaves
        $activeLeaves = Leaves::where('status', 'active')->get();

        $this->leaveBalances = $activeLeaves->map(function ($leave) {
            // Calculate days used for this leave type
            $daysUsed = Employeeleaves::where('employee_id', $this->employee_id)
                ->where('leave_id', $leave->id)
                ->where('status', '!=', 'Rejected')
                ->sum('days');

            // Calculate balance
            $balance = max($leave->days - $daysUsed, 0);

            return [
                'id' => $leave->id,
                'name' => $leave->name,
                'description' => $leave->description,
                'total_days' => $leave->days,
                'used_days' => $daysUsed,
                'balance' => $balance,
                'gender' => $leave->gender,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.hr.leave.leavebalance');
    }
}
