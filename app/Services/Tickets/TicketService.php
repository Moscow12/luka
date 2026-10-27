<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
use App\Models\SmsApiSetting;
use App\Models\Ticket;
use App\Models\TicketAssignmentLog;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\TicketParticipant;
use App\Models\TicketSequence;
use App\Models\TicketStatusLog;
use App\Models\User;
use App\Support\EncryptedFileStore;
use App\Support\HtmlSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The only place ticket state changes. Every change writes the matching status/assignment log.
 */
class TicketService
{
    public function __construct(protected TicketDutySync $dutySync, protected EncryptedFileStore $files) {}

    /**
     * @param  array{subject:string, description:string, category_id:string, priority:string, department_id:string, assignee_employee_id?:?string, employee_assigned_duty_id?:?string, due_date:string, visibility:string}  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function create(array $data, User $actor, array $files = []): Ticket
    {
        return DB::transaction(function () use ($data, $actor, $files) {
            $ticket = Ticket::create([
                'ticket_number' => $this->nextNumber(),
                'subject' => $data['subject'],
                'description' => HtmlSanitizer::clean($data['description']),
                'category_id' => $data['category_id'],
                'priority' => $data['priority'],
                'department_id' => $data['department_id'],
                'due_date' => $data['due_date'],
                'visibility' => $data['visibility'],
                'status' => TicketStatus::Open,
                'created_by' => $actor->id,
            ]);

            $this->logStatus($ticket, null, TicketStatus::Open, $actor);
            $this->setParticipant($ticket, $actor->id, 'creator');
            $this->storeAttachments($ticket, $files, $actor);

            if (! empty($data['assignee_employee_id'])) {
                $this->applyAssignment($ticket, $data['department_id'], $data['assignee_employee_id'], $data['employee_assigned_duty_id'] ?? null, $actor, 'Initial assignment');
            } else {
                // Routed to the department queue
                $this->writeAssignmentLog($ticket, null, null, null, $data['department_id'], $actor, 'Routed to department queue');
            }

            return $ticket->fresh();
        });
    }

    /**
     * Assign or delegate. A null employee sends the ticket back to the department queue.
     */
    public function assign(Ticket $ticket, string $departmentId, ?string $employeeId, User $actor, ?string $reason = null, ?string $dutyId = null): Ticket
    {
        if (! TicketAccess::canManage($actor, $ticket)) {
            throw new TicketActionException('Only the creator, a department supervisor or an admin can assign this ticket.');
        }

        if (in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed, TicketStatus::Cancelled], true)) {
            throw new TicketActionException('A '.$ticket->status->label().' ticket cannot be reassigned.');
        }

        return DB::transaction(function () use ($ticket, $departmentId, $employeeId, $actor, $reason, $dutyId) {
            $this->applyAssignment($ticket, $departmentId, $employeeId, $dutyId, $actor, $reason);

            return $ticket->fresh();
        });
    }

    /**
     * Generic status change (accept, start, pending response, resolve, cancel).
     */
    public function transition(Ticket $ticket, TicketStatus $target, User $actor, ?string $remarks = null): Ticket
    {
        if (in_array($target, [TicketStatus::Closed, TicketStatus::Reopened, TicketStatus::Assigned], true)) {
            throw new TicketActionException('Use the dedicated action to '.strtolower($target->label()).' a ticket.');
        }

        $this->guardTransition($ticket, $target, $actor);

        return DB::transaction(function () use ($ticket, $target, $actor, $remarks) {
            $attributes = ['status' => $target];

            match ($target) {
                TicketStatus::Accepted => $attributes['accepted_at'] = now(),
                TicketStatus::InProgress => $attributes['started_at'] = $ticket->started_at ?? now(),
                TicketStatus::Resolved => $attributes['resolved_at'] = now(),
                default => null,
            };

            $this->changeStatus($ticket, $attributes, $actor, $remarks);

            if ($target === TicketStatus::Cancelled) {
                $this->dutySync->sync($ticket->duty);
            }

            return $ticket->fresh();
        });
    }

    public function close(Ticket $ticket, User $actor, int $rating, ?string $remarks = null): Ticket
    {
        if ($rating < 1 || $rating > 5) {
            throw new TicketActionException('Quality rating must be between 1 and 5.');
        }

        $this->guardTransition($ticket, TicketStatus::Closed, $actor);

        return DB::transaction(function () use ($ticket, $actor, $rating, $remarks) {
            $this->changeStatus($ticket, [
                'status' => TicketStatus::Closed,
                'closed_at' => now(),
                'closed_by' => $actor->id,
                'quality_rating' => $rating,
                'closing_remarks' => $remarks,
            ], $actor, $remarks);

            $this->dutySync->sync($ticket->duty);

            return $ticket->fresh();
        });
    }

    public function reopen(Ticket $ticket, User $actor, string $reason): Ticket
    {
        if (trim($reason) === '') {
            throw new TicketActionException('Please give a reason for reopening.');
        }

        $this->guardTransition($ticket, TicketStatus::Reopened, $actor);

        return DB::transaction(function () use ($ticket, $actor, $reason) {
            $this->changeStatus($ticket, [
                'status' => TicketStatus::Reopened,
                'resolved_at' => null,
                'reopen_count' => $ticket->reopen_count + 1,
            ], $actor, $reason);

            $this->postMessage($ticket, $actor, '<p><strong>Reopened:</strong> '.e($reason).'</p>');

            return $ticket->fresh();
        });
    }

    /** Priority / due date changes by a manager (audited through the activity log). */
    public function updateDetails(Ticket $ticket, User $actor, string $priority, string $dueDate): Ticket
    {
        if (! TicketAccess::canManage($actor, $ticket)) {
            throw new TicketActionException('You are not allowed to change this ticket.');
        }

        if ($ticket->status->isTerminal()) {
            throw new TicketActionException('A '.$ticket->status->label().' ticket cannot be changed.');
        }

        $ticket->update(['priority' => $priority, 'due_date' => $dueDate]);

        return $ticket->fresh();
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function postMessage(Ticket $ticket, User $actor, string $body, string $type = 'public', array $files = []): TicketMessage
    {
        if ($type === 'private_note' && ! TicketAccess::canViewPrivateNotes($actor, $ticket)) {
            throw new TicketActionException('You are not allowed to post private notes on this ticket.');
        }

        $body = HtmlSanitizer::clean($body);
        if (HtmlSanitizer::isBlank($body) && $files === []) {
            throw new TicketActionException('The message is empty.');
        }

        return DB::transaction(function () use ($ticket, $actor, $body, $type, $files) {
            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $actor->id,
                'body' => $body,
                'type' => $type,
            ]);

            $this->storeAttachments($ticket, $files, $actor, $message);

            // Posting makes you a participant and marks the thread read for you
            $participant = TicketParticipant::firstOrCreate(
                ['ticket_id' => $ticket->id, 'user_id' => $actor->id],
                ['role' => 'collaborator']
            );
            $participant->update(['last_read_at' => now()]);

            $ticket->touch();

            return $message;
        });
    }

    public function markRead(Ticket $ticket, User $actor): void
    {
        TicketParticipant::where('ticket_id', $ticket->id)
            ->where('user_id', $actor->id)
            ->update(['last_read_at' => now()]);
    }

    public function addParticipant(Ticket $ticket, User $actor, string $userId, string $role = 'collaborator'): void
    {
        if (! TicketAccess::canManage($actor, $ticket) && ! TicketAccess::isAssignee($actor, $ticket)) {
            throw new TicketActionException('You are not allowed to add participants.');
        }

        if (! in_array($role, ['collaborator', 'watcher'], true)) {
            throw new TicketActionException('Invalid participant role.');
        }

        $existing = TicketParticipant::where('ticket_id', $ticket->id)->where('user_id', $userId)->first();
        if ($existing && in_array($existing->role, ['creator', 'assignee'], true)) {
            return;
        }

        $this->setParticipant($ticket, $userId, $role);
    }

    /**
     * Read the decrypted contents of an attachment.
     */
    public function attachmentContents(TicketAttachment $attachment): string
    {
        return $this->files->get($attachment->path);
    }

    // ------------------------------------------------------------------

    protected function nextNumber(): string
    {
        $year = (int) now()->format('Y');

        TicketSequence::query()->insertOrIgnore(['year' => $year, 'last_number' => 0]);

        $sequence = TicketSequence::where('year', $year)->lockForUpdate()->firstOrFail();
        $next = $sequence->last_number + 1;
        TicketSequence::where('year', $year)->update(['last_number' => $next]);

        return sprintf('HRP-%d-%05d', $year, $next);
    }

    protected function applyAssignment(Ticket $ticket, string $departmentId, ?string $employeeId, ?string $dutyId, User $actor, ?string $reason): void
    {
        $assignee = null;
        if ($employeeId) {
            $assignee = Employee::where('id', $employeeId)->where('department_id', $departmentId)->first();
            if (! $assignee) {
                throw new TicketActionException('The selected officer does not belong to the selected department.');
            }
            if (! $assignee->user_id) {
                throw new TicketActionException('The selected officer has no user account, so they cannot work on tickets.');
            }
        }

        if ($dutyId) {
            if (! $assignee) {
                throw new TicketActionException('A duty can only be linked when the ticket has an assignee.');
            }

            $dutyIsValid = EmployeeAssignedDuty::linkableTo($assignee->id)->where('id', $dutyId)->exists();

            if (! $dutyIsValid) {
                throw new TicketActionException('The selected duty is not an active duty of the assignee.');
            }
        }

        $previousEmployeeId = $ticket->assignee_employee_id;
        $previousDepartmentId = $ticket->department_id;
        $previousDuty = $ticket->duty;

        if ($previousEmployeeId === $employeeId && $previousDepartmentId === $departmentId) {
            // Same assignee: only the duty link can change
            if ($ticket->employee_assigned_duty_id !== $dutyId) {
                $ticket->update(['employee_assigned_duty_id' => $dutyId]);
                $this->dutySync->sync($previousDuty);
                $this->dutySync->sync($ticket->fresh()->duty);
            }

            return;
        }

        // Single accountability: the previous assignee stays on as a collaborator
        if ($previousEmployeeId && $previousEmployeeId !== $employeeId) {
            $previousUserId = Employee::whereKey($previousEmployeeId)->value('user_id');
            if ($previousUserId) {
                TicketParticipant::where('ticket_id', $ticket->id)
                    ->where('user_id', $previousUserId)
                    ->where('role', 'assignee')
                    ->update(['role' => 'collaborator']);
            }
        }

        $newStatus = $assignee ? TicketStatus::Assigned : TicketStatus::Open;

        $this->changeStatus($ticket, [
            'department_id' => $departmentId,
            'assignee_employee_id' => $assignee?->id,
            'employee_assigned_duty_id' => $dutyId,
            'status' => $newStatus,
            'accepted_at' => null,
            'started_at' => null,
        ], $actor, $reason, logIfUnchanged: false);

        if ($assignee) {
            $this->setParticipant($ticket, $assignee->user_id, 'assignee', keepCreator: true);
        }

        $this->writeAssignmentLog($ticket, $previousEmployeeId, $assignee?->id, $previousDepartmentId, $departmentId, $actor, $reason);

        if ($previousDuty && $previousDuty->id !== $dutyId) {
            $this->dutySync->sync($previousDuty);
        }
        $this->dutySync->sync($ticket->fresh()->duty);

        if ($assignee && $ticket->priority === 'critical') {
            $this->notifyCriticalAssignment($ticket, $assignee);
        }
    }

    protected function guardTransition(Ticket $ticket, TicketStatus $target, User $actor): void
    {
        if (! $ticket->status->canTransitionTo($target)) {
            throw new TicketActionException('A '.$ticket->status->label().' ticket cannot be moved to '.$target->label().'.');
        }

        if (! TicketAccess::canMoveTo($actor, $ticket, $target)) {
            throw new TicketActionException('You are not allowed to move this ticket to '.$target->label().'.');
        }
    }

    protected function changeStatus(Ticket $ticket, array $attributes, User $actor, ?string $remarks = null, bool $logIfUnchanged = true): void
    {
        $from = $ticket->status;
        $to = $attributes['status'];

        $ticket->update($attributes);

        if ($from !== $to || $logIfUnchanged) {
            $this->logStatus($ticket, $from, $to, $actor, $remarks);
        }
    }

    protected function logStatus(Ticket $ticket, ?TicketStatus $from, TicketStatus $to, User $actor, ?string $remarks = null): void
    {
        TicketStatusLog::create([
            'ticket_id' => $ticket->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'acted_by' => $actor->id,
            'remarks' => $remarks,
        ]);
    }

    protected function writeAssignmentLog(Ticket $ticket, ?string $prevEmployee, ?string $newEmployee, ?string $prevDepartment, ?string $newDepartment, User $actor, ?string $reason): void
    {
        TicketAssignmentLog::create([
            'ticket_id' => $ticket->id,
            'previous_employee_id' => $prevEmployee,
            'new_employee_id' => $newEmployee,
            'previous_department_id' => $prevDepartment,
            'new_department_id' => $newDepartment,
            'acted_by' => $actor->id,
            'reason' => $reason,
        ]);
    }

    protected function setParticipant(Ticket $ticket, string $userId, string $role, bool $keepCreator = false): void
    {
        $participant = TicketParticipant::firstOrNew(['ticket_id' => $ticket->id, 'user_id' => $userId]);

        // A creator who assigns to themselves keeps the creator role; isAssignee() checks the employee link anyway
        if ($keepCreator && $participant->exists && $participant->role === 'creator') {
            return;
        }

        $participant->role = $role;
        $participant->save();
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    protected function storeAttachments(Ticket $ticket, array $files, User $actor, ?TicketMessage $message = null): void
    {
        foreach ($files as $file) {
            $path = $this->files->put($file, 'tickets/'.$ticket->id);

            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'ticket_message_id' => $message?->id,
                'path' => $path,
                'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $actor->id,
            ]);
        }
    }

    protected function notifyCriticalAssignment(Ticket $ticket, Employee $assignee): void
    {
        if (! SmsApiSetting::where('is_active', true)->exists() || ! function_exists('send_sms_to_staff')) {
            return;
        }

        DB::afterCommit(function () use ($ticket, $assignee) {
            try {
                send_sms_to_staff([$assignee->id], "CRITICAL ticket {$ticket->ticket_number} assigned to you: ".Str::limit($ticket->subject, 80).'. Due '.$ticket->due_date->format('d M Y').'.');
            } catch (\Throwable $e) {
                Log::warning('Ticket SMS failed: '.$e->getMessage());
            }
        });
    }
}
