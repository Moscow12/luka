<?php

namespace App\Livewire\Common\Concerns;

use App\Enums\TicketStatus;
use App\Models\chopactivities;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\payrolls;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketParticipant;
use App\Models\UserNotificationDismissal;
use App\Services\Chat\ChatInbox;
use App\Services\Leave\LeaveApprovalChain;
use App\Services\Tickets\TicketAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait BuildsNotifications
{
    // Sections of the notification list, in display order
    public const SECTION_APPROVALS = 'Awaiting your approval';

    public const SECTION_MY_LEAVE = 'Your leave requests';

    public const SECTION_TICKETS = 'Tickets';

    public const SECTION_OTHER = 'Other';

    protected function buildNotifications(int $perGroup = 10): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $employee = Employee::where('user_id', $user->id)->first();

        $dismissedKeys = UserNotificationDismissal::where('user_id', $user->id)
            ->pluck('notification_key')
            ->toArray();

        $notifications = [];

        // Leave requests whose NEXT approval level is one this user approves
        // (same chain and department rules as the Leave Approval page)
        foreach (app(LeaveApprovalChain::class)->pendingFor($user)->take($perGroup) as $leave) {
            $notifications[] = [
                'key' => 'leave_pending:'.$leave->id,
                'section' => self::SECTION_APPROVALS,
                'type' => 'Leave approval',
                'title' => trim(($leave->employee->first_name ?? '').' '.($leave->employee->last_name ?? ''))
                    .' requested '.($leave->leave->name ?? 'leave'),
                'detail' => $this->leavePeriod($leave).' · Your step: '.($leave->nextApprovalLevel->name ?? 'approval'),
                'time' => $leave->created_at->diffForHumans(),
                'icon' => 'calendar',
                'color' => 'warning',
                'url' => route('leave.leaveapproval'),
                'dismissible' => false,
            ];
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
                    'section' => self::SECTION_APPROVALS,
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
                    'section' => self::SECTION_APPROVALS,
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

        // Decisions on the user's OWN leave requests — dismissible, vanish once opened
        if ($employee) {
            $decided = Employeeleaves::with(['leave', 'approvalnote' => fn ($q) => $q->where('status', 'rejected')])
                ->where('employee_id', $employee->id)
                ->whereRaw('LOWER(status) IN (?, ?)', ['approved', 'rejected'])
                ->where('updated_at', '>=', now()->subDays(60))
                ->orderByDesc('updated_at')
                ->take($perGroup)
                ->get();

            foreach ($decided as $leave) {
                $approved = strtolower($leave->status) === 'approved';
                $key = ($approved ? 'leave_approved:' : 'leave_rejected:').$leave->id;
                if (in_array($key, $dismissedKeys, true)) {
                    continue;
                }

                $reason = $approved ? null : $leave->approvalnote->first()?->comments;

                $notifications[] = [
                    'key' => $key,
                    'section' => self::SECTION_MY_LEAVE,
                    'type' => $approved ? 'Leave approved' : 'Leave rejected',
                    'title' => 'Your '.($leave->leave->name ?? 'leave').' request was '.($approved ? 'approved 🎉' : 'not approved'),
                    'detail' => $this->leavePeriod($leave).($reason ? ' · Reason: '.Str::limit($reason, 80) : ''),
                    'time' => $leave->updated_at->diffForHumans(),
                    'icon' => $approved ? 'calendar-event' : 'calendar-xmark',
                    'color' => $approved ? 'success' : 'danger',
                    'url' => route('leave.requestleave'),
                    'dismissible' => true,
                ];
            }
        }

        // Chat: a single summary line so the bell doesn't fill up with messages
        $unreadChats = app(ChatInbox::class)->totalUnread($user);
        if ($unreadChats > 0) {
            $notifications[] = [
                'key' => 'chat:unread',
                'section' => self::SECTION_OTHER,
                'type' => 'Chat',
                'title' => $unreadChats.' unread chat '.Str::plural('message', $unreadChats),
                'time' => 'now',
                'icon' => 'message',
                'color' => 'primary',
                'url' => route('chat.index'),
                'dismissible' => false,
            ];
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
                'section' => self::SECTION_TICKETS,
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

        // Unassigned tickets waiting in the queue of a department I supervise
        $supervisedDepartments = TicketAccess::supervisedDepartmentIds($user);
        if ($supervisedDepartments !== []) {
            Ticket::whereIn('department_id', $supervisedDepartments)
                ->where('status', TicketStatus::Open->value)
                ->whereNull('assignee_employee_id')
                ->latest()->take($perGroup)->get()
                ->each(fn (Ticket $t) => $push($t, 'queue', 'New Ticket', ' — waiting in your department queue', 'ticket', 'warning', false, $t->created_at));
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

    /** "12 Nov – 16 Nov 2026 · 5 days" */
    protected function leavePeriod(Employeeleaves $leave): string
    {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $days = (int) $leave->days;

        return $start->format($start->isSameYear($end) ? 'd M' : 'd M Y').' – '.$end->format('d M Y')
            .($days ? ' · '.$days.' '.Str::plural('day', $days) : '');
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
