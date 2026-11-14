<?php

namespace App\Services\Engineering;

use App\Enums\Engineering\JobStatus;
use App\Models\Engineering\Equipment;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\OxygenOrder;
use App\Models\Engineering\ProblemCategory;
use App\Models\Engineering\Requisition;
use App\Models\Engineering\Section;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Raises requisitions (MRVs): user requests and oxygen cylinder orders.
 */
class RequisitionService
{
    public function __construct(protected NumberSequence $numbers, protected EngineeringNotifier $notifier) {}

    /**
     * @param  array{section_id:string, category_id:string, emergency:bool, justification?:?string, phone?:?string, department_id?:?string, room_id?:?string, location_text?:?string}  $header
     * @param  array<int, array{equipment_id?:?string, equipment_text?:?string, problem_description:string}>  $items
     */
    public function submit(User $actor, array $header, array $items): Requisition
    {
        $max = config('engineering.max_items_per_requisition');

        if ($items === []) {
            throw new EngineeringActionException('Add at least one item before submitting.');
        }

        if (count($items) > $max) {
            throw new EngineeringActionException("A requisition can carry at most {$max} items.");
        }

        $category = ProblemCategory::where('section_id', $header['section_id'])->find($header['category_id']);
        if (! $category) {
            throw new EngineeringActionException('The problem category does not belong to the selected section.');
        }

        [$priority, $prioritySource] = $this->classify($category, (bool) $header['emergency'], $header['justification'] ?? null);

        $requisition = DB::transaction(function () use ($actor, $header, $items, $category, $priority, $prioritySource) {
            $requisition = $this->createHeader($actor, 'MRV', [
                'source' => 'user_request',
                'priority' => $priority,
                'priority_source' => $prioritySource,
                'escalation_justification' => $prioritySource === 'user_escalated' ? $header['justification'] : null,
                'section_id' => $header['section_id'],
                'category_id' => $category->id,
                'requester_phone' => $header['phone'] ?? null,
                'department_id' => $header['department_id'] ?? null,
                'room_id' => $header['room_id'] ?? null,
                'location_text' => $header['location_text'] ?? null,
            ]);

            foreach (array_values($items) as $index => $item) {
                $equipment = filled($item['equipment_id'] ?? null) ? Equipment::inService()->find($item['equipment_id']) : null;

                $this->createJob($requisition, $index + 1, [
                    'equipment_id' => $equipment?->id,
                    'equipment_text' => $equipment ? null : ($item['equipment_text'] ?? null),
                    'not_in_inventory' => $equipment === null,
                    'problem_description' => $item['problem_description'],
                ]);
            }

            return $requisition;
        });

        if ($requisition->isEmergency()) {
            $this->notifier->holders(
                'engineering-helpdesk',
                "EMERGENCY {$requisition->number}: {$category->name}. {$requisition->locationLabel()}. Requested by {$requisition->requester_name} {$requisition->requester_phone}."
            );
        }

        return $requisition;
    }

    /**
     * Cylinder order from a ward: routed to the oxygen section and delivered as a job.
     *
     * @param  array{cylinder_type:string, qty_ordered:int, emergency:bool, phone?:?string, department_id?:?string, room_id?:?string, location_text?:?string}  $data
     */
    public function orderOxygen(User $actor, array $data): MaintenanceJob
    {
        $section = Section::where('code', config('engineering.oxygen.section'))->first();
        if (! $section) {
            throw new EngineeringActionException('No engineering section is set up to receive oxygen orders.');
        }

        $job = DB::transaction(function () use ($actor, $data, $section) {
            $requisition = $this->createHeader($actor, 'OX', [
                'source' => 'oxygen',
                'priority' => $data['emergency'] ? 'emergency' : 'routine',
                'priority_source' => $data['emergency'] ? 'user_escalated' : null,
                'section_id' => $section->id,
                'requester_phone' => $data['phone'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'room_id' => $data['room_id'] ?? null,
                'location_text' => $data['location_text'] ?? null,
            ]);

            $job = $this->createJob($requisition, 1, [
                'equipment_text' => 'Medical oxygen cylinder - '.$data['cylinder_type'],
                'not_in_inventory' => false,
                'problem_description' => "Deliver {$data['qty_ordered']} x {$data['cylinder_type']} medical oxygen cylinder(s).",
                'type_of_work' => 'other',
                'no_spares_consumed' => true,
            ]);

            OxygenOrder::create([
                'job_id' => $job->id,
                'cylinder_type' => $data['cylinder_type'],
                'qty_ordered' => $data['qty_ordered'],
            ]);

            return $job;
        });

        if ($data['emergency']) {
            $this->notifier->holders('engineering-helpdesk', "EMERGENCY oxygen order {$job->number} for {$job->requisition->locationLabel()}.");
        }

        return $job;
    }

    /**
     * A category on the emergency list always wins. Otherwise the user may escalate, with a reason.
     *
     * @return array{0:string, 1:?string} priority, priority source
     */
    protected function classify(ProblemCategory $category, bool $wantsEmergency, ?string $justification): array
    {
        if ($category->isEmergency()) {
            return ['emergency', 'auto_criterion'];
        }

        if (! $wantsEmergency) {
            return ['routine', null];
        }

        if (mb_strlen(trim((string) $justification)) < 10) {
            throw new EngineeringActionException('Explain in at least 10 characters why this routine category is an emergency.');
        }

        return ['emergency', 'user_escalated'];
    }

    /** Stamps the number, the time and a snapshot of the requester's profile. */
    protected function createHeader(User $actor, string $series, array $attributes): Requisition
    {
        $employee = EngineeringAccess::employee($actor);

        return Requisition::create(array_merge([
            'number' => $this->numbers->next($series),
            'requested_by' => $actor->id,
            'requester_name' => $actor->full_name,
            'requester_role' => $employee?->position?->name,
            'requested_at' => now(),
        ], $attributes, [
            'requester_phone' => ($attributes['requester_phone'] ?? null) ?: ($employee?->phone ?? $actor->phone_number),
            'department_id' => ($attributes['department_id'] ?? null) ?: $employee?->department_id,
        ]));
    }

    /** PPM requisition raised from a schedule: its own number series, counted as an MRV everywhere. */
    public function raisePpm(User $actor, Equipment $equipment, string $frequencyLabel): MaintenanceJob
    {
        $requisition = $this->createHeader($actor, 'PPM', [
            'source' => 'ppm',
            'priority' => 'routine',
            'section_id' => $equipment->section_id,
            'department_id' => $equipment->department_id,
            'room_id' => $equipment->room_id,
        ]);

        return $this->createJob($requisition, 1, [
            'equipment_id' => $equipment->id,
            'problem_description' => "Planned preventive maintenance ({$frequencyLabel}) for {$equipment->name} {$equipment->code}.",
            'type_of_work' => 'ppm',
        ]);
    }

    protected function createJob(Requisition $requisition, int $seq, array $attributes): MaintenanceJob
    {
        return MaintenanceJob::create(array_merge([
            'requisition_id' => $requisition->id,
            'seq' => $seq,
            'number' => $requisition->number.'-'.$seq,
            'section_id' => $requisition->section_id,
            'status' => JobStatus::Submitted,
        ], $attributes));
    }
}
