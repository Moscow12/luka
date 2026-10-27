<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketStatus;
use App\Models\departments;
use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
use App\Models\Ticket;
use App\Models\TicketParticipant;
use App\Services\Tickets\TicketAccess;
use App\Services\Tickets\TicketActionException;
use App\Services\Tickets\TicketService;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketView extends Component
{
    use WithFileUploads;

    public string $ticketId;

    // Messages read before this page load; newer ones are marked "new"
    public ?string $readMarker = null;

    // Composer
    public $newMessage = '';

    public $isPrivate = false;

    public $messageAttachments = [];

    // Modals: status | close | reopen | assign | details | participant
    public $modal = null;

    public $targetStatus = null;

    public $rating = 5;

    public $remarks = '';

    public $assignDepartment = '';

    public $assignEmployee = '';

    public $assignDuty = '';

    public $editPriority = '';

    public $editDueDate = '';

    public $participantUserId = '';

    public $participantRole = 'collaborator';

    public function mount(Ticket $ticket)
    {
        $user = Auth::user();
        abort_unless(TicketAccess::canView($user, $ticket), 403);

        $this->ticketId = $ticket->id;

        // Supervisors/admins get a watcher row so read status is tracked for them too
        if (TicketAccess::canManage($user, $ticket)) {
            TicketParticipant::firstOrCreate(['ticket_id' => $ticket->id, 'user_id' => $user->id], ['role' => 'watcher']);
        }

        $this->readMarker = TicketParticipant::where('ticket_id', $ticket->id)->where('user_id', $user->id)->value('last_read_at');
        app(TicketService::class)->markRead($ticket, $user);
    }

    protected function ticket(): Ticket
    {
        return Ticket::findOrFail($this->ticketId);
    }

    public function refreshThread(TicketService $service)
    {
        $service->markRead($this->ticket(), Auth::user());
    }

    // ---------------------------------------------------------------- messages

    public function sendMessage(TicketService $service)
    {
        $this->validate([
            'newMessage' => ['nullable', 'string', 'max:65000'],
            'messageAttachments' => ['array', 'max:10'],
            'messageAttachments.*' => CreateTicket::ATTACHMENT_RULES,
        ]);

        try {
            $service->postMessage(
                $this->ticket(),
                Auth::user(),
                (string) $this->newMessage,
                $this->isPrivate ? 'private_note' : 'public',
                $this->messageAttachments
            );
            $this->reset(['newMessage', 'messageAttachments', 'isPrivate']);
        } catch (TicketActionException $e) {
            $this->addError('newMessage', $e->getMessage());
        }
    }

    public function removeMessageAttachment($index)
    {
        unset($this->messageAttachments[$index]);
        $this->messageAttachments = array_values($this->messageAttachments);
    }

    // ---------------------------------------------------------------- status

    public function quickTransition($status, TicketService $service)
    {
        $target = TicketStatus::from($status);

        // Cancelling or blocking needs a reason
        if (in_array($target, [TicketStatus::Cancelled, TicketStatus::PendingResponse, TicketStatus::Resolved], true)) {
            $this->openModal('status');
            $this->targetStatus = $target->value;

            return;
        }

        $this->run(fn () => $service->transition($this->ticket(), $target, Auth::user()), 'Ticket moved to '.$target->label().'.');
    }

    public function submitStatus(TicketService $service)
    {
        $target = TicketStatus::from($this->targetStatus);
        $this->validate(['remarks' => [$target === TicketStatus::Cancelled ? 'required' : 'nullable', 'string', 'max:2000']]);

        $this->run(function () use ($service, $target) {
            $ticket = $service->transition($this->ticket(), $target, Auth::user(), $this->remarks ?: null);
            if ($this->remarks) {
                $service->postMessage($ticket, Auth::user(), '<p><strong>'.e($target->label()).':</strong> '.e($this->remarks).'</p>');
            }
        }, 'Ticket moved to '.$target->label().'.');
    }

    public function submitClose(TicketService $service)
    {
        $this->validate(['rating' => ['required', 'integer', 'between:1,5'], 'remarks' => ['nullable', 'string', 'max:2000']]);

        $this->run(fn () => $service->close($this->ticket(), Auth::user(), (int) $this->rating, $this->remarks ?: null), 'Ticket closed.');
    }

    public function submitReopen(TicketService $service)
    {
        $this->validate(['remarks' => ['required', 'string', 'max:2000']]);

        $this->run(fn () => $service->reopen($this->ticket(), Auth::user(), $this->remarks), 'Ticket reopened.');
    }

    // ---------------------------------------------------------------- assignment & details

    public function openModal($modal)
    {
        $this->resetErrorBag();
        $this->modal = $modal;
        $this->remarks = '';
        $this->rating = 5;

        $ticket = $this->ticket();
        if ($modal === 'assign') {
            $this->assignDepartment = $ticket->department_id;
            $this->assignEmployee = $ticket->assignee_employee_id ?? '';
            $this->assignDuty = $ticket->employee_assigned_duty_id ?? '';
        }
        if ($modal === 'details') {
            $this->editPriority = $ticket->priority;
            $this->editDueDate = $ticket->due_date->toDateString();
        }
        if ($modal === 'participant') {
            $this->participantUserId = '';
            $this->participantRole = 'collaborator';
        }
    }

    public function closeModal()
    {
        $this->modal = null;
        $this->targetStatus = null;
    }

    public function updatedAssignDepartment()
    {
        $this->assignEmployee = '';
        $this->assignDuty = '';
    }

    public function updatedAssignEmployee()
    {
        $this->assignDuty = '';
    }

    public function submitAssign(TicketService $service)
    {
        $this->validate([
            'assignDepartment' => ['required', 'exists:departments,id'],
            'assignEmployee' => ['nullable', 'exists:employees,id'],
            'assignDuty' => ['nullable', 'exists:employee_assigned_duties,id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->run(fn () => $service->assign(
            $this->ticket(),
            $this->assignDepartment,
            $this->assignEmployee ?: null,
            Auth::user(),
            $this->remarks ?: null,
            $this->assignDuty ?: null
        ), 'Assignment updated.');
    }

    public function submitDetails(TicketService $service)
    {
        $this->validate([
            'editPriority' => ['required', 'in:low,medium,high,critical'],
            'editDueDate' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $this->run(fn () => $service->updateDetails($this->ticket(), Auth::user(), $this->editPriority, $this->editDueDate), 'Ticket updated.');
    }

    public function submitParticipant(TicketService $service)
    {
        $this->validate([
            'participantUserId' => ['required', 'exists:users,id'],
            'participantRole' => ['required', 'in:collaborator,watcher'],
        ]);

        $this->run(fn () => $service->addParticipant($this->ticket(), Auth::user(), $this->participantUserId, $this->participantRole), 'Participant added.');
    }

    protected function run(callable $action, string $success): void
    {
        try {
            $action();
            session()->flash('success', $success);
            $this->closeModal();
        } catch (TicketActionException $e) {
            session()->flash('error', $e->getMessage());
            $this->closeModal();
        }
    }

    // ---------------------------------------------------------------- render

    public function render()
    {
        $user = Auth::user();
        $ticket = Ticket::with([
            'category', 'department', 'assignee', 'creator', 'closedBy', 'duty',
            'attachments' => fn ($q) => $q->whereNull('ticket_message_id'),
            'participants.user:id,first_name,surname',
            'statusLogs.actor:id,first_name,surname',
            'assignmentLogs.actor:id,first_name,surname',
            'assignmentLogs.previousEmployee:id,first_name,last_name',
            'assignmentLogs.newEmployee:id,first_name,last_name',
            'assignmentLogs.newDepartment:id,name',
        ])->findOrFail($this->ticketId);

        // Visibility may have changed since mount (e.g. reassigned away)
        abort_unless(TicketAccess::canView($user, $ticket), 403);

        $canSeePrivate = TicketAccess::canViewPrivateNotes($user, $ticket);
        $messages = $ticket->messages()
            ->with(['user:id,first_name,surname,profile_picture', 'attachments'])
            ->when(! $canSeePrivate, fn ($q) => $q->where('type', 'public'))
            ->orderBy('created_at')
            ->get();

        $canManage = TicketAccess::canManage($user, $ticket);
        $lastMessageAt = $messages->max('created_at');

        $history = collect()
            ->merge($ticket->statusLogs->map(fn ($log) => [
                'at' => $log->created_at,
                'icon' => 'fa-arrow-right-arrow-left',
                'text' => ($log->from_status ? TicketStatus::from($log->from_status)->label().' → ' : '').TicketStatus::from($log->to_status)->label(),
                'by' => trim(($log->actor?->first_name ?? '').' '.($log->actor?->surname ?? '')),
                'remarks' => $log->remarks,
            ]))
            ->merge($ticket->assignmentLogs->map(fn ($log) => [
                'at' => $log->created_at,
                'icon' => 'fa-user-pen',
                'text' => ($log->previousEmployee ? $log->previousEmployee->first_name.' '.$log->previousEmployee->last_name : 'Queue')
                    .' → '.($log->newEmployee ? $log->newEmployee->first_name.' '.$log->newEmployee->last_name : ($log->newDepartment?->name.' queue')),
                'by' => trim(($log->actor?->first_name ?? '').' '.($log->actor?->surname ?? '')),
                'remarks' => $log->reason,
            ]))
            ->sortByDesc('at')
            ->values();

        $assignEmployees = $this->modal === 'assign' && $this->assignDepartment
            ? Employee::where('department_id', $this->assignDepartment)->where('status', 'Active')->whereNotNull('user_id')->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name'])
            : collect();

        $assignDuties = $this->modal === 'assign' && $this->assignEmployee
            ? EmployeeAssignedDuty::linkableTo($this->assignEmployee)->orderBy('end_date')->get(['id', 'duty_name', 'measurement_type'])
            : collect();

        $participantCandidates = $this->modal === 'participant'
            ? Employee::whereNotNull('user_id')->where('status', 'Active')
                ->whereNotIn('user_id', $ticket->participants->pluck('user_id'))
                ->orderBy('first_name')->get(['user_id', 'first_name', 'last_name'])
            : collect();

        return view('livewire.tickets.ticket-view', [
            'ticket' => $ticket,
            'messages' => $messages,
            'history' => $history,
            'lastMessageAt' => $lastMessageAt,
            'canSeePrivate' => $canSeePrivate,
            'canManage' => $canManage,
            'canAddParticipants' => $canManage || TicketAccess::isAssignee($user, $ticket),
            'isAssignee' => TicketAccess::isAssignee($user, $ticket),
            'transitions' => TicketAccess::availableTransitions($user, $ticket),
            'canReassign' => $canManage && ! in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed, TicketStatus::Cancelled], true),
            'departments' => $this->modal === 'assign' ? departments::orderBy('name')->get(['id', 'name']) : collect(),
            'assignEmployees' => $assignEmployees,
            'assignDuties' => $assignDuties,
            'participantCandidates' => $participantCandidates,
            'descriptionHtml' => HtmlSanitizer::clean($ticket->description),
        ]);
    }
}
