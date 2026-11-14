<?php

namespace App\Services\Engineering;

use App\Enums\Engineering\JobStatus;
use App\Enums\Engineering\SatisfactionRating;
use App\Models\Employee;
use App\Models\Engineering\Feedback;
use App\Models\Engineering\JobAssignmentLog;
use App\Models\Engineering\JobNote;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Requisition;
use App\Models\Engineering\SafetyTest;
use App\Models\Engineering\Section;
use App\Models\User;
use App\Support\Engineering\Eng;
use App\Support\Engineering\Lookups;
use Illuminate\Support\Facades\DB;

/**
 * The only place a job changes state: assignment, work, closure, feedback and rework.
 */
class JobService
{
    public function __construct(protected EngineeringNotifier $notifier, protected InventoryService $inventory) {}

    /* ---------- Assignment ---------- */

    /**
     * First assignment of a submitted job. This starts the TAT clock.
     *
     * @param  array{type_of_work:string, section_id:string, engineer_id:string, assistant_ids?:array<int,string>, days_to_be_done:int, note?:?string}  $data
     */
    public function assign(MaintenanceJob $job, User $actor, array $data): MaintenanceJob
    {
        if ($job->status !== JobStatus::Submitted) {
            throw new EngineeringActionException('This job is already assigned. Use job re-assignment to move it.');
        }

        if (! EngineeringAccess::canAssign($actor, $data['section_id'])) {
            throw new EngineeringActionException('You are not authorised to assign jobs for this section.');
        }

        return DB::transaction(fn () => $this->applyAssignment($job, $actor, $data, 'assigned'));
    }

    /**
     * Moves an open job to another engineer. Notes, spares and tests stay with the job.
     *
     * @param  array{section_id:string, engineer_id:string, assistant_ids?:array<int,string>, reason:string, keep_clock?:bool}  $data
     */
    public function reassign(MaintenanceJob $job, User $actor, array $data): MaintenanceJob
    {
        if (! in_array($job->status->value, JobStatus::workableValues(), true)) {
            throw new EngineeringActionException('Only an open, assigned job can be re-assigned.');
        }

        if (! EngineeringAccess::canAssign($actor, $job->section_id)) {
            throw new EngineeringActionException('You are not authorised to re-assign this job.');
        }

        if (blank($data['reason'] ?? null)) {
            throw new EngineeringActionException('State the reason for re-assigning this job.');
        }

        if ($data['engineer_id'] === $job->engineer_id) {
            throw new EngineeringActionException('Choose a different engineer to re-assign this job to.');
        }

        $previous = $job->engineer;

        $job = DB::transaction(fn () => $this->applyAssignment(
            $job,
            $actor,
            array_merge($data, ['type_of_work' => $job->type_of_work, 'days_to_be_done' => $job->days_to_be_done, 'note' => $job->assignment_note]),
            'reassigned',
            $data['reason'],
            $data['keep_clock'] ?? true,
        ));

        $this->notifier->employee($previous, "Job {$job->number} has been re-assigned to ".Eng::name($job->engineer).'.');

        return $job;
    }

    /**
     * System assignment of an auto-generated PPM job to the responsible engineer.
     * Returns false when that engineer cannot take it, leaving the job in the helpdesk queue.
     */
    public function autoAssign(MaintenanceJob $job, ?Employee $engineer, User $actor): bool
    {
        if (! $engineer) {
            return false;
        }

        try {
            DB::transaction(fn () => $this->applyAssignment($job, $actor, [
                'type_of_work' => $job->type_of_work ?? 'ppm',
                'section_id' => $job->section_id,
                'engineer_id' => $engineer->id,
                'days_to_be_done' => config('engineering.lead_time_days.routine'),
                'note' => 'Assigned automatically to the engineer responsible for this PPM.',
            ], 'assigned'));

            return true;
        } catch (EngineeringActionException) {
            return false;
        }
    }

    /** Helpdesk correction of the priority, with a reason kept on the job thread. */
    public function reclassify(Requisition $requisition, User $actor, string $priority, string $reason): void
    {
        if (! EngineeringAccess::canAssign($actor, $requisition->section_id)) {
            throw new EngineeringActionException('Only the helpdesk, the head of section or the HOD may re-classify a requisition.');
        }

        if (blank($reason)) {
            throw new EngineeringActionException('State the reason for changing the priority.');
        }

        if ($requisition->priority === $priority) {
            return;
        }

        DB::transaction(function () use ($requisition, $actor, $priority, $reason) {
            $requisition->update(['priority' => $priority, 'priority_source' => 'helpdesk_set']);

            foreach ($requisition->jobs as $job) {
                $this->writeNote($job, $actor, 'Priority changed to '.Lookups::PRIORITIES[$priority].": {$reason}", 'system');
            }
        });
    }

    /* ---------- Working the job ---------- */

    /** First time one of its engineers opens the job, work has started. */
    public function open(MaintenanceJob $job, User $actor): void
    {
        if (EngineeringAccess::canWorkJob($actor, $job) && in_array($job->status, [JobStatus::Assigned, JobStatus::Rework], true)) {
            $job->update([
                'status' => JobStatus::InProgress,
                'started_at' => $job->started_at ?? now(),
                'last_activity_at' => now(),
            ]);
        }
    }

    public function addNote(MaintenanceJob $job, User $actor, string $body): JobNote
    {
        $this->assertCanWork($job, $actor);

        return $this->writeNote($job, $actor, $body, 'engineer');
    }

    /** A requester may add a follow-up comment to their own open job; it never changes the status. */
    public function addRequesterNote(MaintenanceJob $job, User $actor, string $body): JobNote
    {
        if (! EngineeringAccess::isRequester($actor, $job) || $job->isLocked()) {
            throw new EngineeringActionException('You can only comment on your own open requisitions.');
        }

        return $this->writeNote($job, $actor, $body, 'requester');
    }

    public function saveSafetyTest(MaintenanceJob $job, User $actor, string $type, bool $completed, bool $notApplicable = false, ?string $comment = null): void
    {
        $this->assertCanWork($job, $actor);

        if ($notApplicable && blank($comment)) {
            throw new EngineeringActionException('Explain why the test is not applicable.');
        }

        $done = $completed || $notApplicable;

        SafetyTest::updateOrCreate(['job_id' => $job->id, 'test_type' => $type], [
            'completed' => $completed && ! $notApplicable,
            'not_applicable' => $notApplicable,
            'comment' => $comment,
            'completed_by' => $done ? $actor->id : null,
            'completed_at' => $done ? now() : null,
        ]);

        $this->touch($job);
    }

    /** Tells the requester that getting the spare has been initiated. */
    public function informClient(MaintenanceJob $job, User $actor): void
    {
        $this->assertCanWork($job, $actor);

        $job->update(['client_informed_at' => now(), 'last_activity_at' => now()]);

        $this->notifier->requester($job, "{$job->number}: a spare part is required for {$job->equipmentName()}. Sourcing it from the store has been initiated; we will update you.");
    }

    /* ---------- Closure ---------- */

    /**
     * Final status and sign-out. "Job complete" and "beyond repair" stop the TAT clock;
     * "additional assistance needed" keeps the job open and escalates it.
     *
     * @param  array{final_status:string, explanation?:?string, recommendation?:?string, equipment_status?:?string, qty_delivered?:?int, received_by?:?string}  $data
     */
    public function close(MaintenanceJob $job, User $actor, array $data): MaintenanceJob
    {
        $this->assertCanWork($job, $actor);

        return match ($data['final_status']) {
            'assistance_needed' => $this->requestAssistance($job, $actor, $data),
            'job_complete', 'beyond_repair' => $this->signOut($job, $actor, $data),
        };
    }

    /**
     * What still blocks closing the job as complete, as human-readable reasons.
     *
     * @return array<int, string>
     */
    public function completionBlockers(MaintenanceJob $job): array
    {
        if ($job->oxygenOrder) {
            return [];
        }

        $blockers = [];

        if (in_array($job->section->code, config('engineering.safety_test_sections'), true)) {
            $satisfied = $job->safetyTests->filter->isSatisfied()->pluck('test_type')->all();

            foreach (array_diff_key(Lookups::SAFETY_TESTS, array_flip($satisfied)) as $label) {
                $blockers[] = "Safety testing: {$label}.";
            }
        }

        if (! $job->no_spares_consumed && ! $job->sparesRequisitions()->exists()) {
            $blockers[] = 'Record the spares used, or tick "No spare(s) consumed for this service".';
        }

        if ($job->ppmInstance && ! $job->ppmInstance->isCompleted()) {
            $blockers[] = 'Complete the PPM checklist.';
        }

        return $blockers;
    }

    /* ---------- Feedback and rework ---------- */

    /** The requester rates a finished job. From here the MRV is read-only for everyone. */
    public function giveFeedback(MaintenanceJob $job, User $actor, SatisfactionRating $rating, ?string $comment): Feedback
    {
        if (! EngineeringAccess::isRequester($actor, $job)) {
            throw new EngineeringActionException('Only the person who raised the requisition can rate it.');
        }

        if (EngineeringAccess::isOnJob($actor, $job)) {
            throw new EngineeringActionException('You cannot rate work you carried out yourself.');
        }

        if (! $job->awaitsFeedback()) {
            throw new EngineeringActionException('This requisition is not waiting for feedback.');
        }

        if ($rating->requiresComment() && blank($comment)) {
            throw new EngineeringActionException('Please explain what went wrong when rating '.strtolower($rating->label()).'.');
        }

        return DB::transaction(function () use ($job, $actor, $rating, $comment) {
            $feedback = Feedback::create([
                'job_id' => $job->id,
                'rating' => $rating,
                'score_pct' => $rating->score(),
                'comment' => $comment,
                'given_by' => $actor->id,
            ]);

            if ($job->status === JobStatus::Completed) {
                $job->update(['status' => JobStatus::Closed]);
            }

            return $feedback;
        });
    }

    /**
     * Sends a finished job back to the same engineer. The MRV number is kept and the job is
     * permanently marked RE-WORK. A frozen engineer sends it to the helpdesk queue instead.
     */
    public function rework(MaintenanceJob $job, User $actor, string $reason): MaintenanceJob
    {
        if (! EngineeringAccess::isRequester($actor, $job) && ! EngineeringAccess::canAssign($actor, $job->section_id)) {
            throw new EngineeringActionException('You are not authorised to send this job back for rework.');
        }

        if ($job->isOpen()) {
            throw new EngineeringActionException('Only finished work can be sent back for rework.');
        }

        if (blank($reason)) {
            throw new EngineeringActionException('State why the job is being sent back.');
        }

        $engineer = $job->engineer()->first();
        $engineerAvailable = $engineer && $engineer->status === 'Active';

        DB::transaction(function () use ($job, $actor, $reason, $engineer, $engineerAvailable) {
            // The old rating stays on record but no longer counts
            $job->feedback()->where('superseded', false)->update(['superseded' => true, 'resent_for_rework' => true]);
            $job->safetyTests()->update(['completed' => false, 'not_applicable' => false, 'completed_by' => null, 'completed_at' => null]);

            $job->update([
                'status' => $engineerAvailable ? JobStatus::Rework : JobStatus::Submitted,
                'is_rework' => true,
                'rework_count' => $job->rework_count + 1,
                'engineer_id' => $engineerAvailable ? $engineer->id : null,
                'final_status' => null,
                'assistance_explanation' => null,
                'signed_out_by' => null,
                'completed_at' => null,
                'last_activity_at' => null,
            ]);

            if (! $engineerAvailable) {
                $job->assistants()->detach();
            }

            $this->logAssignment($job, $engineerAvailable ? 'rework' : 'returned_to_queue', $engineer?->id, $engineerAvailable ? $engineer->id : null, $actor, $reason);
            $this->writeNote($job, $actor, "Sent back for rework: {$reason}", 'system');
        });

        $message = "RE-WORK {$job->number} ({$job->equipmentName()}): {$reason}";
        if ($engineerAvailable) {
            $this->notifier->employee($engineer, $message);
        }
        $this->notifier->supervisors($job, $message);

        return $job->refresh();
    }

    /* ---------- Internals ---------- */

    protected function applyAssignment(MaintenanceJob $job, User $actor, array $data, string $action, ?string $reason = null, bool $keepClock = true): MaintenanceJob
    {
        $section = Section::findOrFail($data['section_id']);
        $pool = $section->assignableStaff()->pluck('employees.id')->all();

        if (! in_array($data['engineer_id'], $pool, true)) {
            throw new EngineeringActionException("The engineer must be an active member of the {$section->name} section.");
        }

        $assistants = array_values(array_diff(array_unique($data['assistant_ids'] ?? []), [$data['engineer_id']]));
        if (array_diff($assistants, $pool) !== []) {
            throw new EngineeringActionException("Assistants must be active members of the {$section->name} section.");
        }

        $previousEngineerId = $job->engineer_id;
        $now = now();

        $job->update([
            'status' => match ($job->status) {
                JobStatus::Rework, JobStatus::AwaitingSpares => $job->status,
                default => JobStatus::Assigned,
            },
            'type_of_work' => $data['type_of_work'],
            'section_id' => $section->id,
            'engineer_id' => $data['engineer_id'],
            'assigned_by' => $actor->id,
            'assigned_at' => $now,
            'original_assigned_at' => $keepClock ? ($job->original_assigned_at ?? $now) : $now,
            'days_to_be_done' => $data['days_to_be_done'],
            'assignment_note' => $data['note'] ?? null,
            'last_activity_at' => null,
        ]);

        $job->assistants()->sync($assistants);
        $this->logAssignment($job, $action, $previousEngineerId, $data['engineer_id'], $actor, $reason);

        $job->refresh();
        $requisition = $job->requisition;

        $this->notifier->employee($job->engineer, sprintf(
            '%s job %s assigned to you: %s at %s. Requested by %s %s. Days allowed: %d.',
            strtoupper($requisition->priority),
            $job->number,
            $job->equipmentName(),
            $requisition->locationLabel(),
            $requisition->requester_name,
            $requisition->requester_phone,
            $job->days_to_be_done,
        ));

        return $job;
    }

    protected function requestAssistance(MaintenanceJob $job, User $actor, array $data): MaintenanceJob
    {
        if (blank($data['explanation'] ?? null)) {
            throw new EngineeringActionException('Explain what additional assistance is needed.');
        }

        $job->update([
            'status' => JobStatus::AssistanceNeeded,
            'final_status' => 'assistance_needed',
            'assistance_explanation' => $data['explanation'],
            'recommendation' => $data['recommendation'] ?? $job->recommendation,
            'last_activity_at' => now(),
        ]);

        $this->notifier->supervisors($job, "{$job->number} needs additional assistance: {$data['explanation']}");

        return $job;
    }

    protected function signOut(MaintenanceJob $job, User $actor, array $data): MaintenanceJob
    {
        $complete = $data['final_status'] === 'job_complete';
        $oxygen = $job->oxygenOrder;

        if ($complete && ($blockers = $this->completionBlockers($job)) !== []) {
            throw new EngineeringActionException('The job cannot be closed as complete yet. '.implode(' ', $blockers));
        }

        if ($complete && $oxygen && (empty($data['qty_delivered']) || blank($data['received_by'] ?? null))) {
            throw new EngineeringActionException('Record the quantity delivered and the ward staff who received the cylinders.');
        }

        DB::transaction(function () use ($job, $actor, $data, $complete, $oxygen) {
            $job->update([
                'status' => $complete ? JobStatus::Completed : JobStatus::BeyondRepair,
                'final_status' => $data['final_status'],
                'assistance_explanation' => null,
                'recommendation' => $data['recommendation'] ?? null,
                'signed_out_by' => $actor->id,
                'completed_at' => now(),
                'last_activity_at' => now(),
            ]);

            if ($equipment = $job->equipment) {
                $status = $complete ? ($data['equipment_status'] ?? 'working') : 'out_of_service';
                $this->inventory->changeStatus($equipment, $status, "Set on closure of {$job->number}", $actor);

                if (! $complete) {
                    $equipment->update(['flagged_for_removal' => true]);
                }
            }

            if ($complete && $oxygen) {
                $oxygen->update([
                    'qty_delivered' => $data['qty_delivered'],
                    'received_by' => $data['received_by'],
                    'delivered_by' => EngineeringAccess::employee($actor)?->id,
                    'delivered_at' => now(),
                ]);
            }
        });

        $this->notifier->requester($job, "{$job->number} ({$job->equipmentName()}) has been signed out as ".strtolower(Lookups::FINAL_STATUSES[$data['final_status']]).'. Please rate the service under Engineering > Processed requisitions.');

        return $job->refresh();
    }

    protected function assertCanWork(MaintenanceJob $job, User $actor): void
    {
        if ($job->isLocked()) {
            throw new EngineeringActionException('This job has been signed out and can no longer be changed.');
        }

        if (! EngineeringAccess::isOnJob($actor, $job)) {
            throw new EngineeringActionException('Only the assigned engineer or an assistant can work on this job.');
        }
    }

    protected function writeNote(MaintenanceJob $job, User $actor, string $body, string $kind): JobNote
    {
        $note = JobNote::create(['job_id' => $job->id, 'kind' => $kind, 'body' => trim($body), 'created_by' => $actor->id]);

        if ($kind === 'engineer') {
            $this->touch($job);
        }

        return $note;
    }

    protected function logAssignment(MaintenanceJob $job, string $action, ?string $fromId, ?string $toId, User $actor, ?string $reason): void
    {
        JobAssignmentLog::create([
            'job_id' => $job->id,
            'action' => $action,
            'from_engineer_id' => $fromId,
            'to_engineer_id' => $toId,
            'section_id' => $job->section_id,
            'actor_id' => $actor->id,
            'reason' => $reason,
        ]);
    }

    protected function touch(MaintenanceJob $job): void
    {
        $job->forceFill(['last_activity_at' => now()])->saveQuietly();
    }
}
