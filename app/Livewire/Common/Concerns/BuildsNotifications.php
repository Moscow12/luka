<?php

namespace App\Livewire\Common\Concerns;

use App\Enums\TicketStatus;
use App\Models\approvalleveltodocument;
use App\Models\approvalleveltoemployee;
use App\Models\chopactivities;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\leaverequestapproval;
use App\Models\payrolls;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketParticipant;
use App\Models\UserNotificationDismissal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        // Tickets: new assignments, overdue work, resolutions awaiting my verification, unread replies
        foreach ($this->buildTicketNotifications($user, $employee, $dismissedKeys, $perGroup) as $notification) {
            $notifications[] = $notification;
        }

        return $notifications;
    }

    protected function buildTicketNotifications($user, ?Employee $employee, array $dismissedKeys, int $perGroup): array
    {
        $items = [];
        $push = function (Ticket $ticket, string $event, string $type, string $title, string $icon, string $color, bool $dismissible, $time) use (&$items, $dismissedKeys) {
            $key = 'ticket:'.$ticket->id.':'.$event;
            if ($dismissible && in_array($key, $dismissedKeys, true)) {
                return;
            }
            $items[] = [
                'key' => $key,
                'type' => $type,
                'title' => $ticket->ticket_number.' · '.Str::limit($ticket->subject, 60).$title,
                'time' => $time->diffForHumans(),
                'icon' => $icon,
                'color' => $color,
                'url' => route('tickets.view', $ticket),
                'dismissible' => $dismissible,
            ];
        };

        if ($employee) {
            Ticket::where('assignee_employee_id', $employee->id)
                ->where('status', TicketStatus::Assigned->value)
                ->latest('updated_at')->take($perGroup)->get()
                ->each(fn (Ticket $t) => $push($t, 'assigned', 'Ticket Assigned', ' — awaiting your acceptance', 'ticket', 'primary', false, $t->updated_at));

            Ticket::where('assignee_employee_id', $employee->id)
                ->overdue()
                ->orderBy('due_date')->take($perGroup)->get()
                ->each(fn (Ticket $t) => $push($t, 'overdue:'.$t->due_date->toDateString(), 'Ticket Overdue', ' — was due '.$t->due_date->format('d M'), 'alert-triangle', 'danger', true, $t->due_date));
        }

        Ticket::where('created_by', $user->id)
            ->where('status', TicketStatus::Resolved->value)
            ->latest('resolved_at')->take($perGroup)->get()
            ->each(fn (Ticket $t) => $push($t, 'resolved', 'Ticket Resolved', ' — please verify and close', 'circle-check', 'success', false, $t->resolved_at));

        // Unread public replies on tickets I take part in
        TicketParticipant::where('user_id', $user->id)
            ->whereHas('ticket', fn ($q) => $q->whereNotIn('status', [TicketStatus::Closed->value, TicketStatus::Cancelled->value]))
            ->with('ticket')
            ->get()
            ->each(function (TicketParticipant $participant) use ($user, $push) {
                $latest = TicketMessage::where('ticket_id', $participant->ticket_id)
                    ->where('type', 'public')
                    ->where('user_id', '!=', $user->id)
                    ->when($participant->last_read_at, fn ($q) => $q->where('created_at', '>', $participant->last_read_at))
                    ->latest()
                    ->first(['id', 'created_at']);

                if ($latest) {
                    $push($participant->ticket, 'message:'.$latest->id, 'Ticket Reply', ' — new message', 'message', 'info', true, $latest->created_at);
                }
            });

        return array_slice($items, 0, $perGroup * 3);
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
