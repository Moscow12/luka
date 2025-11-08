<?php

namespace App\Livewire\Common;

use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\approvalleveltoemployee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsList extends Component
{
    public $pendingApprovals = [];

    protected $listeners = ['refreshNotifications' => 'loadPendingApprovals'];

    public function mount()
    {
        $this->loadPendingApprovals();
    }

    public function loadPendingApprovals()
    {
        // Get logged-in user's employee record
        $employee = Employee::where('user_id', Auth::id())->first();

        if (!$employee) {
            $this->pendingApprovals = [];
            return;
        }

        // Get all approval levels assigned to this employee
        $approvalLevels = approvalleveltoemployee::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->with('approval_level.approvalleveltodocuments')
            ->get();

        $documentTypes = [];
        foreach ($approvalLevels as $mapping) {
            if ($mapping->approval_level) {
                foreach ($mapping->approval_level->approvalleveltodocuments as $doc) {
                    if ($doc->is_active) {
                        $documentTypes[] = $doc->document_type;
                    }
                }
            }
        }

        $documentTypes = array_unique($documentTypes);

        // Get pending leave requests if "Leave" is in document types
        $this->pendingApprovals = [];

        if (in_array('Leave', $documentTypes)) {
            $pendingLeaves = Employeeleaves::with(['employee', 'leave'])
                ->where('status', 'Awaiting')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            foreach ($pendingLeaves as $leave) {
                $this->pendingApprovals[] = [
                    'type' => 'Leave',
                    'title' => ($leave->employee->getFullName() ?? 'Unknown') . ' requested ' . ($leave->leave->name ?? 'leave'),
                    'time' => $leave->created_at->diffForHumans(),
                    'icon' => 'calendar',
                    'color' => 'info',
                    'url' => route('hr.staffdetails', ['id' => $leave->employee_id]),
                ];
            }
        }
    }

    public function render()
    {
        return view('livewire.common.notifications-list');
    }
}
