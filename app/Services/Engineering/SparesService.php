<?php

namespace App\Services\Engineering;

use App\Enums\Engineering\JobStatus;
use App\Models\Engineering\JobNote;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\SparesLine;
use App\Models\Engineering\SparesRequisition;
use App\Models\products;
use App\Models\StockLedgerControl;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Spares requisitions from a job to the engineering store, and the store keeper's response.
 */
class SparesService
{
    public function __construct(protected NumberSequence $numbers, protected EngineeringNotifier $notifier) {}

    /**
     * "Spare required": sends the lines to the store and parks the job as awaiting spares.
     *
     * @param  array<int, array{product_id?:?string, spare_name?:?string, uom?:?string, item_code?:?string, quantity:float|int|string, date_consumed?:?string}>  $lines
     */
    public function send(MaintenanceJob $job, User $actor, array $lines): SparesRequisition
    {
        $this->assertCanWork($job, $actor);

        if ($lines === []) {
            throw new EngineeringActionException('Add at least one spare before sending the requisition.');
        }

        $requisition = DB::transaction(function () use ($job, $actor, $lines) {
            $requisition = SparesRequisition::create([
                'number' => $this->numbers->next('SR'),
                'job_id' => $job->id,
                'requested_by' => $actor->id,
                'requested_at' => now(),
            ]);

            foreach (array_values($lines) as $index => $line) {
                // Stock items take their name, code and unit from the item master
                $product = filled($line['product_id'] ?? null) ? products::find($line['product_id']) : null;

                SparesLine::create([
                    'spares_requisition_id' => $requisition->id,
                    'seq' => $index + 1,
                    'product_id' => $product?->id,
                    'spare_name' => $product?->name ?? $line['spare_name'],
                    'uom' => $product?->unit ?? ($line['uom'] ?? null),
                    'item_code' => $product?->code ?? ($line['item_code'] ?? null),
                    'qty_requested' => $line['quantity'],
                    'date_consumed' => $line['date_consumed'] ?? null,
                ]);
            }

            $job->update([
                'status' => JobStatus::AwaitingSpares,
                'spare_required_at' => $job->spare_required_at ?? now(),
                'no_spares_consumed' => false,
                'last_activity_at' => now(),
            ]);

            return $requisition;
        });

        $this->notifier->holders('engineering-store', "Spares requisition {$requisition->number} for {$job->number} ({$job->equipmentName()}) is waiting in the engineering store.");

        return $requisition;
    }

    /** "No spare(s) consumed for this service" - mutually exclusive with spares lines. */
    public function markNoSparesConsumed(MaintenanceJob $job, User $actor, bool $none): void
    {
        $this->assertCanWork($job, $actor);

        if ($none && $job->sparesRequisitions()->exists()) {
            throw new EngineeringActionException('Spares have already been requested for this job.');
        }

        $job->update(['no_spares_consumed' => $none, 'last_activity_at' => now()]);
    }

    /* ---------- Store keeper ---------- */

    /** Issues all or part of a line. */
    public function issue(SparesLine $line, User $actor, float $quantity): void
    {
        $this->assertStoreKeeper($actor, $line);

        $outstanding = $line->qty_requested - $line->qty_issued;
        if ($quantity <= 0 || $quantity > $outstanding) {
            throw new EngineeringActionException("Issue a quantity between 0 and {$outstanding}.");
        }

        $issued = $line->qty_issued + $quantity;

        $this->resolve($line, $actor, [
            'qty_issued' => $issued,
            'status' => $issued >= $line->qty_requested ? 'issued' : 'partial',
            'date_consumed' => $line->date_consumed ?? now()->toDateString(),
        ]);
    }

    public function reject(SparesLine $line, User $actor, string $reason): void
    {
        $this->assertStoreKeeper($actor, $line);

        if (blank($reason)) {
            throw new EngineeringActionException('Give the reason for rejecting this line.');
        }

        $this->resolve($line, $actor, ['status' => 'rejected', 'reject_reason' => $reason]);
    }

    /** The store cannot supply: the line goes onto the job card for external procurement. */
    public function sendToProcurement(SparesLine $line, User $actor): void
    {
        $this->assertStoreKeeper($actor, $line);

        $this->resolve($line, $actor, ['status' => 'to_procure']);
    }

    /** Quantity on hand across stores, from the latest stock ledger balance of each store. */
    public function stockOnHand(?string $productId): ?float
    {
        if (! $productId) {
            return null;
        }

        return (float) StockLedgerControl::where('item_id', $productId)
            ->orderByDesc('created_at')
            ->get(['department_store_id', 'post_balance'])
            ->unique('department_store_id')
            ->sum('post_balance');
    }

    /* ---------- Internals ---------- */

    protected function resolve(SparesLine $line, User $actor, array $attributes): void
    {
        DB::transaction(function () use ($line, $actor, $attributes) {
            $line->update(array_merge($attributes, ['issued_by' => $actor->id, 'issued_at' => now()]));

            $requisition = $line->requisition;
            $requisition->update(['status' => $this->requisitionStatus($requisition)]);

            $this->releaseJobIfServed($requisition->job, $actor);
        });

        $requisition = $line->requisition->refresh();
        if (! $requisition->isPending()) {
            $this->notifier->user($requisition->requestedBy, "Spares requisition {$requisition->number} for {$requisition->job->number} has been processed by the store.");
        }
    }

    protected function requisitionStatus(SparesRequisition $requisition): string
    {
        $statuses = $requisition->lines()->pluck('status');

        return match (true) {
            $statuses->contains('requested') => $statuses->intersect(['issued', 'partial'])->isNotEmpty() ? 'partially_issued' : 'requested',
            $statuses->contains('partial') => 'partially_issued',
            $statuses->contains('to_procure') => 'to_procure',
            default => 'issued',
        };
    }

    /** Once nothing is outstanding from the store or procurement, the engineer can carry on. */
    protected function releaseJobIfServed(MaintenanceJob $job, User $actor): void
    {
        $outstanding = SparesLine::whereIn('status', ['requested', 'partial', 'to_procure'])
            ->whereHas('requisition', fn ($q) => $q->where('job_id', $job->id))
            ->exists();

        if (! $outstanding && $job->status === JobStatus::AwaitingSpares) {
            $job->update(['status' => JobStatus::InProgress]);
        }

        // Separation of duties: issuing against your own job is allowed but always flagged
        if (EngineeringAccess::isOnJob($actor, $job)) {
            JobNote::create([
                'job_id' => $job->id,
                'kind' => 'system',
                'body' => 'Spares for this job were processed in the store by a member of the job team.',
                'created_by' => $actor->id,
            ]);
        }
    }

    protected function assertCanWork(MaintenanceJob $job, User $actor): void
    {
        if (! EngineeringAccess::canWorkJob($actor, $job)) {
            throw new EngineeringActionException('Only the assigned engineer or an assistant can request spares for an open job.');
        }
    }

    protected function assertStoreKeeper(User $actor, SparesLine $line): void
    {
        if (! EngineeringAccess::canRunStore($actor)) {
            throw new EngineeringActionException('Only the engineering store keeper can process spares requisitions.');
        }

        if (! in_array($line->status, ['requested', 'partial'], true)) {
            throw new EngineeringActionException('This line has already been processed.');
        }
    }
}
