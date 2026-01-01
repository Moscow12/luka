<?php

namespace App\Livewire\Common;

use App\Models\chopactivities;
use App\Models\Employeeleaves;
use App\Models\payrolls;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationsList extends Component
{
    public $pendingApprovals = [];

    public function mount()
    {
        $this->loadPendingApprovals();
    }

    #[On('refreshNotifications')]
    public function loadPendingApprovals()
    {
        $user = Auth::user();

        if (! $user) {
            $this->pendingApprovals = [];

            return;
        }

        $this->pendingApprovals = [];
        $isSuperAdmin = $user->isSuperAdmin();

        // Leave approvals
        if ($isSuperAdmin || $user->can('approve-leave')) {
            $pendingLeaves = Employeeleaves::with(['employee', 'leave'])
                ->whereIn('status', ['Awaiting', 'pending', 'Active'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            foreach ($pendingLeaves as $leave) {
                $this->pendingApprovals[] = [
                    'type' => 'Leave',
                    'title' => ($leave->employee->first_name ?? '').' '.($leave->employee->last_name ?? '').' requested '.($leave->leave->name ?? 'leave'),
                    'time' => $leave->created_at->diffForHumans(),
                    'icon' => 'calendar',
                    'color' => 'info',
                    'url' => route('leave.leaveapproval'),
                ];
            }
        }

        // Payroll approvals
        if ($isSuperAdmin || $user->can('approve-payroll')) {
            $pendingPayrolls = payrolls::with(['employee'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            foreach ($pendingPayrolls as $payroll) {
                $this->pendingApprovals[] = [
                    'type' => 'Payroll',
                    'title' => 'Payroll for '.($payroll->employee->first_name ?? '').' '.($payroll->employee->last_name ?? '').' - '.$payroll->period,
                    'time' => $payroll->created_at->diffForHumans(),
                    'icon' => 'currency-dollar',
                    'color' => 'success',
                    'url' => route('payrollgeneration'),
                ];
            }
        }

        // Note: Roster and Allowance tables don't have approval workflow status columns
        // They are managed differently and don't need notification approvals

        // CHOP approvals
        if ($isSuperAdmin || $user->can('approve-chop')) {
            $pendingChop = chopactivities::where('is_approved', false)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            foreach ($pendingChop as $chop) {
                $this->pendingApprovals[] = [
                    'type' => 'CHOP',
                    'title' => 'CHOP Activity: '.($chop->planned_activity ?? 'Pending review'),
                    'time' => $chop->created_at->diffForHumans(),
                    'icon' => 'file-invoice',
                    'color' => 'danger',
                    'url' => route('chop.director.review'),
                ];
            }
        }

        // Sort by most recent
        usort($this->pendingApprovals, function ($a, $b) {
            return 0; // Keep current order (each type is already sorted by created_at desc)
        });
    }

    public function render()
    {
        return view('livewire.common.notifications-list');
    }
}
