<?php

namespace App\Services\Engineering;

use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\PpmEntry;
use App\Models\Engineering\PpmInstance;
use App\Models\Engineering\PpmSchedule;
use App\Models\User;
use App\Support\Engineering\Lookups;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Planned preventive maintenance: due dates, per-occurrence checklists and PPM requisitions.
 */
class PpmService
{
    public function __construct(protected RequisitionService $requisitions, protected JobService $jobs) {}

    /**
     * Next due date for a calendar frequency, counted from the last PPM (or installation if
     * never done). Running-hours schedules are due on a date entered by the user.
     */
    public function nextDue(PpmSchedule $schedule): ?Carbon
    {
        $months = Lookups::PPM_FREQUENCY_MONTHS[$schedule->frequency] ?? null;
        if ($months === null) {
            return $schedule->next_due_at;
        }

        $from = $schedule->last_done_at ?? $schedule->equipment->installation_date;

        return $from->copy()->addMonths($months)->startOfDay();
    }

    /**
     * Raises the PPM requisition for the current occurrence and hands it to the responsible
     * engineer. A frozen engineer leaves it in the helpdesk queue. Never raised twice.
     */
    public function generateRequisition(PpmSchedule $schedule, User $actor): MaintenanceJob
    {
        if ($schedule->performer !== 'engineering') {
            throw new EngineeringActionException('User PPM is done by the equipment user on the checklist; it does not need a requisition.');
        }

        $instance = $this->openChecklist($schedule);
        if ($instance->job) {
            return $instance->job;
        }

        return DB::transaction(function () use ($schedule, $actor, $instance) {
            $job = $this->requisitions->raisePpm($actor, $schedule->equipment, Lookups::PPM_FREQUENCIES[$schedule->frequency]);
            $instance->update(['job_id' => $job->id]);

            $this->jobs->autoAssign($job, $schedule->responsible?->status === 'Active' ? $schedule->responsible : null, $actor);

            return $job->refresh();
        });
    }

    /** The checklist of the current occurrence: a fresh blank copy of the task list when none is open. */
    public function openChecklist(PpmSchedule $schedule): PpmInstance
    {
        if ($instance = $schedule->openInstance) {
            return $instance;
        }

        if ($schedule->tasks->isEmpty()) {
            throw new EngineeringActionException('Add the checklist tasks for this PPM before it can be started.');
        }

        return DB::transaction(function () use ($schedule) {
            $instance = PpmInstance::create(['schedule_id' => $schedule->id, 'due_at' => $schedule->next_due_at]);

            foreach ($schedule->tasks as $task) {
                PpmEntry::create([
                    'instance_id' => $instance->id,
                    'seq' => $task->seq,
                    'description' => $task->description,
                    'expected_value' => $task->expected_value,
                    'mandatory' => $task->mandatory,
                ]);
            }

            $schedule->unsetRelation('openInstance');

            return $instance;
        });
    }

    /**
     * @param  array{done:bool, not_applicable:bool, value?:?string, comment?:?string}  $data
     */
    public function saveEntry(PpmEntry $entry, User $actor, array $data): void
    {
        $instance = PpmInstance::findOrFail($entry->instance_id);
        $this->assertMayFill($instance, $actor);

        if ($data['not_applicable'] && blank($data['comment'] ?? null)) {
            throw new EngineeringActionException('A task can only be marked N/A with a comment.');
        }

        $ticked = $data['done'] || $data['not_applicable'];

        $entry->update([
            'done' => $data['done'] && ! $data['not_applicable'],
            'not_applicable' => $data['not_applicable'],
            'value' => $data['value'] ?? null,
            'comment' => $data['comment'] ?? null,
            'ticked_by' => $ticked ? $actor->id : null,
            'ticked_at' => $ticked ? now() : null,
        ]);

        if ($instance->state === 'blank') {
            $instance->update(['state' => 'in_progress']);
        }
    }

    /** Completes the occurrence once every mandatory task is ticked, and moves the schedule on. */
    public function complete(PpmInstance $instance, User $actor): void
    {
        $this->assertMayFill($instance, $actor);

        $missing = $instance->entries->reject->isSatisfied()->count();
        if ($missing > 0) {
            throw new EngineeringActionException("{$missing} mandatory task(s) are still unticked.");
        }

        DB::transaction(function () use ($instance, $actor) {
            $instance->update(['state' => 'completed', 'completed_at' => now(), 'completed_by' => $actor->id]);

            $schedule = $instance->schedule;
            $schedule->update(['last_done_at' => now(), 'last_done_by' => $actor->id]);
            $schedule->update(['next_due_at' => $this->nextDue($schedule->refresh())]);
        });
    }

    /**
     * Required consumables against what the store holds.
     *
     * @return array<int, array{name:string, code:?string, uom:?string, required:float, in_store:?float, short:bool}>
     */
    public function consumablesStatus(PpmSchedule $schedule, SparesService $spares): array
    {
        return $schedule->consumables->map(function ($consumable) use ($spares) {
            $inStore = $spares->stockOnHand($consumable->product_id);

            return [
                'product_id' => $consumable->product_id,
                'name' => $consumable->item_name,
                'code' => $consumable->item_code,
                'uom' => $consumable->uom,
                'required' => $consumable->qty_required,
                'in_store' => $inStore,
                'short' => $inStore === null || $inStore < $consumable->qty_required,
            ];
        })->all();
    }

    /**
     * Engineering PPM is filled by the job team (or a supervisor); user PPM by staff of the
     * department that owns the equipment.
     */
    public function canFill(PpmInstance $instance, User $actor): bool
    {
        if ($instance->isCompleted()) {
            return false;
        }

        $schedule = $instance->schedule;

        if (EngineeringAccess::canAssign($actor, $schedule->equipment->section_id)) {
            return true;
        }

        if ($schedule->performer === 'user') {
            return EngineeringAccess::employee($actor)?->department_id === $schedule->equipment->department_id;
        }

        return $instance->job
            ? EngineeringAccess::canWorkJob($actor, $instance->job)
            : EngineeringAccess::employee($actor)?->id === $schedule->responsible_employee_id;
    }

    protected function assertMayFill(PpmInstance $instance, User $actor): void
    {
        if ($instance->isCompleted()) {
            throw new EngineeringActionException('A completed checklist is read-only.');
        }

        if (! $this->canFill($instance, $actor)) {
            throw new EngineeringActionException('You are not responsible for this PPM checklist.');
        }
    }
}
