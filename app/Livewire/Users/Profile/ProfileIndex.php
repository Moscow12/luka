<?php

namespace App\Livewire\Users\Profile;

use App\Models\activitypersonel;
use App\Models\Employee;
use App\Models\employeeattendances;
use App\Models\Employeeleaves;
use App\Models\employeeroster;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProfileIndex extends Component
{
    use WithPagination;

    // Active tab state
    public $activeTab = 'personal';

    // Employee data
    public $employee;

    // Statistics
    public $stats = [];

    // Pagination theme
    protected $paginationTheme = 'bootstrap';

    /**
     * Mount the component and load initial data
     */
    public function mount()
    {
        $this->loadEmployeeData();
        $this->calculateStatistics();
    }

    /**
     * Load employee data for the authenticated user
     */
    public function loadEmployeeData()
    {
        $userId = Auth::id();

        // Find employee record linked to the authenticated user
        // Note: The Employee model has 'user_id' field but the relationship
        // in the model uses 'added_by'. We'll search by user_id in the fillable fields.
        $this->employee = Employee::with([
            'department',
            'position', // Job title
            'designation',
            'workstation',
            'country',
            'region',
            'district',
            'ward',
            'vilstreet',
            'denomination',
        ])->where('user_id', $userId)->first();

        // If no employee record found, try to get basic user info
        if (!$this->employee) {
            // Employee record not found for this user
            // You may want to handle this case differently
            $this->employee = null;
        }
    }

    /**
     * Calculate statistics for the dashboard cards
     */
    public function calculateStatistics()
    {
        if (!$this->employee) {
            $this->stats = [
                'total_leaves' => 0,
                'pending_leaves' => 0,
                'approved_leaves' => 0,
                'rejected_leaves' => 0,
                'total_rosters' => 0,
                'upcoming_rosters' => 0,
                'total_activities' => 0,
                'total_attendances' => 0,
                'attendance_rate' => 0,
            ];

            return;
        }

        // Leave statistics
        $totalLeaves = Employeeleaves::where('employee_id', $this->employee->id)->count();
        $pendingLeaves = Employeeleaves::where('employee_id', $this->employee->id)
            ->where('status', 'pending')
            ->count();
        $approvedLeaves = Employeeleaves::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->count();
        $rejectedLeaves = Employeeleaves::where('employee_id', $this->employee->id)
            ->where('status', 'rejected')
            ->count();

        // Roster statistics
        $totalRosters = employeeroster::where('employee_id', $this->employee->id)->count();
        $upcomingRosters = employeeroster::where('employee_id', $this->employee->id)
            ->where('roster_date', '>=', now())
            ->count();

        // CHOP Activities statistics (based on job title)
        $totalActivities = 0;
        if ($this->employee->title_id) {
            $totalActivities = activitypersonel::where('title_id', $this->employee->title_id)->count();
        }

        // Attendance statistics (based on fingerprint ID)
        $totalAttendances = 0;
        $attendanceRate = 0;
        if ($this->employee->fpid) {
            $totalAttendances = employeeattendances::where('fpuser_id', $this->employee->fpid)->count();

            // Calculate attendance rate (present days / total working days in last 30 days)
            $thirtyDaysAgo = now()->subDays(30);
            $attendancesLast30Days = employeeattendances::where('fpuser_id', $this->employee->fpid)
                ->where('clockdate', '>=', $thirtyDaysAgo)
                ->distinct('clockdate')
                ->count('clockdate');

            // Assuming 22 working days per month (approximate)
            $workingDays = 22;
            $attendanceRate = $workingDays > 0 ? round(($attendancesLast30Days / $workingDays) * 100, 1) : 0;
        }

        $this->stats = [
            'total_leaves' => $totalLeaves,
            'pending_leaves' => $pendingLeaves,
            'approved_leaves' => $approvedLeaves,
            'rejected_leaves' => $rejectedLeaves,
            'total_rosters' => $totalRosters,
            'upcoming_rosters' => $upcomingRosters,
            'total_activities' => $totalActivities,
            'total_attendances' => $totalAttendances,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Get leave requests for the employee
     */
    public function getLeaveRequestsProperty()
    {
        if (!$this->employee) {
            return collect([]);
        }

        return Employeeleaves::with(['leave', 'approved_by_user', 'approvalnote'])
            ->where('employee_id', $this->employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'leavePage');
    }

    /**
     * Get roster assignments for the employee
     */
    public function getRosterAssignmentsProperty()
    {
        if (!$this->employee) {
            return collect([]);
        }

        return employeeroster::with(['shift', 'department'])
            ->where('employee_id', $this->employee->id)
            ->orderBy('roster_date', 'desc')
            ->paginate(10, ['*'], 'rosterPage');
    }

    /**
     * Get CHOP activities assigned to the employee's job title
     */
    public function getChopActivitiesProperty()
    {
        if (!$this->employee || !$this->employee->title_id) {
            return collect([]);
        }

        return activitypersonel::with(['activity', 'title'])
            ->where('title_id', $this->employee->title_id)
            ->paginate(10, ['*'], 'activityPage');
    }

    /**
     * Get attendance records for the employee
     */
    public function getAttendanceRecordsProperty()
    {
        if (!$this->employee || !$this->employee->fpid) {
            return collect([]);
        }

        return employeeattendances::with('fpuser')
            ->where('fpuser_id', $this->employee->fpid)
            ->orderBy('clockdate', 'desc')
            ->orderBy('clocktime', 'desc')
            ->paginate(15, ['*'], 'attendancePage');
    }

    /**
     * Switch between tabs
     */
    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.users.profile.profile-index', [
            'leaveRequests' => $this->leaveRequests,
            'rosterAssignments' => $this->rosterAssignments,
            'chopActivities' => $this->chopActivities,
            'attendanceRecords' => $this->attendanceRecords,
        ]);
    }
}
