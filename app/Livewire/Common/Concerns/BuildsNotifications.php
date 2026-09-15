<?php

namespace App\Livewire\Common\Concerns;

use App\Models\approvalleveltodocument;
use App\Models\approvalleveltoemployee;
use App\Models\chopactivities;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\leaverequestapproval;
use App\Models\payrolls;
use App\Models\UserNotificationDismissal;
use Illuminate\Support\Facades\Auth;

trait BuildsNotifications
{
    protected function buildNotifications(int $perGroup = 10): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $employee = Employee::where('user_id', $user->id)->first();
        $userApprovalLevels = $employee
            ? approvalleveltoemployee::where('employee_id', $employee->id)
                ->where('is_active', true)
                ->pluck('approval_level_id')
                ->filter()
                ->toArray()
            : [];

        $dismissedKeys = UserNotificationDismissal::where('user_id', $user->id)
            ->pluck('notification_key')
            ->toArray();

        $notifications = [];

        // Leaves awaiting THIS user's approval level
        if ($isSuperAdmin || $user->can('approve-leave')) {
            $pendingLeaves = Employeeleaves::with(['employee', 'leave'])
                ->whereRaw('LOWER(status) IN (?, ?, ?)', ['awaiting', 'pending', 'active'])
                ->orderBy('created_at', 'desc')
                ->get();

            $count = 0;
            foreach ($pendingLeaves as $leave) {
                if ($count >= $perGroup) {
                    break;
                }
                $next = $this->resolveNextLeaveApprovalLevel($leave);
                if (! $next) {
                    continue;
                }
                if (! $isSuperAdmin && ! in_array($next->id, $userApprovalLevels)) {
                    continue;
                }

                $notifications[] = [
                    'key' => 'leave_pending:'.$leave->id,
                    'type' => 'Leave',
                    'title' => trim(($leave->employee->first_name ?? '').' '.($leave->employee->last_name ?? ''))
                        .' requested '.($leave->leave->name ?? 'leave'),
                    'time' => $leave->created_at->diffForHumans(),
                    'icon' => 'calendar',
                    'color' => 'info',
                    'url' => route('leave.leaveapproval'),
                    'dismissible' => false,
                ];
                $count++;
            }
        }

        // Payroll approvals (unchanged)
        if ($isSuperAdmin || $user->can('approve-payroll')) {
            $pendingPayrolls = payrolls::with(['employee'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take($perGroup)
                ->get();

            foreach ($pendingPayrolls as $payroll) {
                $notifications[] = [
                    'key' => 'payroll_pending:'.$payroll->id,
                    'type' => 'Payroll',
                    'title' => 'Payroll for '.trim(($payroll->employee->first_name ?? '').' '.($payroll->employee->last_name ?? '')).' - '.$payroll->period,
                    'time' => $payroll->created_at->diffForHumans(),
                    'icon' => 'currency-dollar',
                    'color' => 'success',
                    'url' => route('payrollgeneration'),
                    'dismissible' => false,
                ];
            }
        }

        // CHOP approvals (unchanged)
        if ($isSuperAdmin || $user->can('approve-chop')) {
            $pendingChop = chopactivities::where('is_approved', false)
                ->orderBy('created_at', 'desc')
                ->take($perGroup)
                ->get();

            foreach ($pendingChop as $chop) {
                $notifications[] = [
                    'key' => 'chop_pending:'.$chop->id,
                    'type' => 'CHOP',
                    'title' => 'CHOP Activity: '.($chop->planned_activity ?? 'Pending review'),
                    'time' => $chop->created_at->diffForHumans(),
                    'icon' => 'file-invoice',
                    'color' => 'danger',
                    'url' => route('chop.director.review'),
                    'dismissible' => false,
                ];
            }
        }

        // The user's OWN approved leaves — dismissible, vanish after opening
        if ($employee) {
            $approvedLeaves = Employeeleaves::with(['leave'])
                ->where('employee_id', $employee->id)
                ->whereRaw('LOWER(status) = ?', ['approved'])
                ->orderBy('updated_at', 'desc')
                ->take($perGroup)
                ->get();

            foreach ($approvedLeaves as $leave) {
                $key = 'leave_approved:'.$leave->id;
                if (in_array($key, $dismissedKeys, true)) {
                    continue;
                }
                $notifications[] = [
                    'key' => $key,
                    'type' => 'Leave Approved',
                    'title' => 'Your '.($leave->leave->name ?? 'leave').' request has been approved',
                    'time' => $leave->updated_at->diffForHumans(),
                    'icon' => 'calendar-event',
                    'color' => 'success',
                    'url' => route('leave.requestleave'),
                    'dismissible' => true,
                ];
            }
        }

        return $notifications;
    }

    protected function resolveNextLeaveApprovalLevel($leave)
    {
        $approvalLevels = approvalleveltodocument::where('document_type', 'Leave')
            ->where('is_active', true)
            ->with('approval_level')
            ->get()
            ->pluck('approval_level')
            ->filter()
            ->sortBy('level_order')
            ->values();

        if ($approvalLevels->isEmpty()) {
            return null;
        }

        $existingApprovals = leaverequestapproval::where('leave_request_id', $leave->id)->get();

        if ($existingApprovals->where('status', 'rejected')->isNotEmpty()) {
            return null;
        }

        foreach ($approvalLevels as $level) {
            $alreadyApproved = $existingApprovals
                ->where('approval_level_id', $level->id)
                ->where('status', 'approved')
                ->isNotEmpty();

            if (! $alreadyApproved) {
                return $level;
            }
        }

        return null;
    }

    public function dismissNotification(string $key): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        UserNotificationDismissal::firstOrCreate(
            ['user_id' => $user->id, 'notification_key' => $key],
            ['dismissed_at' => now()]
        );
    }
}
