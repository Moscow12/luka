<?php

namespace App\Services\Engineering;

use App\Enums\Engineering\JobStatus;
use App\Models\Engineering\JobCard;
use App\Models\Engineering\JobCardApproval;
use App\Models\Engineering\JobCardItem;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\SparesLine;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Job card lifecycle: draft -> certification -> authorisation -> purchasing -> delivery.
 */
class JobCardService
{
    public function __construct(protected NumberSequence $numbers, protected EngineeringNotifier $notifier) {}

    /** The job's card, raised on first use and pre-filled from the job and its unsupplied spares. */
    public function forJob(MaintenanceJob $job, User $actor): JobCard
    {
        if ($job->jobCard) {
            return $job->jobCard;
        }

        if (! EngineeringAccess::canWorkJob($actor, $job)) {
            throw new EngineeringActionException('Only the assigned engineer or an assistant can raise a job card.');
        }

        if (! in_array($job->status, [JobStatus::InProgress, JobStatus::AwaitingSpares], true)) {
            throw new EngineeringActionException('A job card can only be raised for a job that is in progress or awaiting spares.');
        }

        return DB::transaction(function () use ($job) {
            $card = JobCard::create([
                'number' => $this->numbers->next('JC'),
                'job_id' => $job->id,
                'complaints_faults' => $job->problem_description,
                'requesting_engineer_id' => $job->engineer_id,
                'date_part_requested' => ($job->spare_required_at ?? now())->toDateString(),
            ]);

            SparesLine::where('status', 'to_procure')
                ->whereHas('requisition', fn ($q) => $q->where('job_id', $job->id))
                ->get()
                ->each(fn (SparesLine $line, int $index) => JobCardItem::create([
                    'job_card_id' => $card->id,
                    'seq' => $index + 1,
                    'description' => $line->spare_name,
                    'uom' => $line->uom,
                    'item_code' => $line->item_code,
                    'quantity' => $line->qty_requested - $line->qty_issued,
                ]));

            return $card->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data  narrative and costing fields
     * @param  array<int, array{description:string, uom?:?string, item_code?:?string, quantity:float|int|string, price:float|int|string}>  $items
     */
    public function save(JobCard $card, User $actor, array $data, array $items): JobCard
    {
        $this->assertEngineerCanEdit($card, $actor);

        return DB::transaction(function () use ($card, $data, $items) {
            $card->update($data);

            $card->items()->delete();
            foreach (array_values($items) as $index => $item) {
                JobCardItem::create([
                    'job_card_id' => $card->id,
                    'seq' => $index + 1,
                    'description' => $item['description'],
                    'uom' => $item['uom'] ?? null,
                    'item_code' => $item['item_code'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            $card->recalculate();

            return $card->refresh();
        });
    }

    /** Sends the costed card for certification: this is "procurement order made" on the job. */
    public function submit(JobCard $card, User $actor): JobCard
    {
        $this->assertEngineerCanEdit($card, $actor);

        if (blank($card->failure_reason)) {
            throw new EngineeringActionException('Select the reason of failure before submitting.');
        }

        if ($card->failure_reason === 'other' && blank($card->failure_reason_text)) {
            throw new EngineeringActionException('Describe the reason of failure when "Others" is selected.');
        }

        if ($card->grand_total <= 0) {
            throw new EngineeringActionException('Capture the items or charges before submitting: the estimated grand total is zero.');
        }

        DB::transaction(function () use ($card) {
            $card->update([
                'status' => 'pending_certification',
                'revision' => $card->status === 'returned' ? $card->revision + 1 : $card->revision,
                'submitted_at' => now(),
            ]);

            $card->job->update([
                'status' => JobStatus::AwaitingSpares,
                'procurement_order_at' => $card->job->procurement_order_at ?? now(),
                'last_activity_at' => now(),
            ]);
        });

        $this->notifier->holders('engineering-certify', "Job card {$card->number} for {$card->job->number} is waiting for certification.");

        return $card->refresh();
    }

    /**
     * Certifier or authoriser decision on the stage the card is currently at.
     *
     * @param  string  $decision  approved | returned | rejected
     */
    public function decide(JobCard $card, User $actor, string $decision, ?string $comment): JobCard
    {
        $stage = match ($card->status) {
            'pending_certification' => 'certify',
            'pending_authorisation' => 'authorise',
            default => throw new EngineeringActionException('This job card is not waiting for a decision.'),
        };

        $this->assertMayDecide($card, $actor, $stage);

        if ($decision !== 'approved' && blank($comment)) {
            throw new EngineeringActionException('A comment is required when returning or rejecting a job card.');
        }

        DB::transaction(function () use ($card, $actor, $stage, $decision, $comment) {
            JobCardApproval::create([
                'job_card_id' => $card->id,
                'revision' => $card->revision,
                'stage' => $stage,
                'decision' => $decision,
                'actor_id' => $actor->id,
                'comment' => $comment,
            ]);

            $card->update(match (true) {
                $decision === 'returned' => ['status' => 'returned'],
                $decision === 'rejected' => ['status' => 'rejected'],
                $stage === 'certify' => ['status' => 'pending_authorisation'],
                default => ['status' => 'approved', 'approved_at' => now()],
            });
        });

        $card->refresh();

        match ($card->status) {
            'pending_authorisation' => $this->notifier->holders('engineering-authorise', "Job card {$card->number} is certified and waiting for authorisation."),
            'approved' => $this->notifier->holders('engineering-purchasing', "Job card {$card->number} is authorised and ready for purchasing."),
            default => $this->notifier->employee($card->requestingEngineer, "Job card {$card->number} was {$decision}: {$comment}"),
        };

        return $card;
    }

    /**
     * Purchasing trail kept by the purchasing office. Delivery releases the job from "awaiting spares".
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePurchasing(JobCard $card, User $actor, array $data): JobCard
    {
        if (! EngineeringAccess::canPurchase($actor)) {
            throw new EngineeringActionException('Only the purchasing office can update purchasing information.');
        }

        if (! $card->isApproved()) {
            throw new EngineeringActionException('Purchasing can only be recorded on an authorised job card.');
        }

        $this->assertDelaysExplained($card, $data);

        $wasDelivered = $card->delivery_actual_at !== null;

        $card->update(array_merge($data, [
            'status' => match (true) {
                filled($data['delivery_actual_at'] ?? null) => 'delivered',
                filled($data['order_number'] ?? null) || filled($data['order_date'] ?? null) => 'purchasing',
                default => 'approved',
            },
        ]));

        if (! $wasDelivered && $card->delivery_actual_at) {
            $this->onDelivered($card);
        }

        return $card->refresh();
    }

    /* ---------- Internals ---------- */

    protected function onDelivered(JobCard $card): void
    {
        $job = $card->job;

        if ($job->status === JobStatus::AwaitingSpares) {
            $job->update(['status' => JobStatus::InProgress]);
        }

        $message = "Materials for job card {$card->number} ({$job->number}, {$job->equipmentName()}) have been delivered.";
        $this->notifier->employee($job->engineer, $message);
        $this->notifier->requester($job, $message.' The engineer will now complete the work.');
    }

    /** A stage that ran past its allowed days must carry a reason before it can be saved. */
    protected function assertDelaysExplained(JobCard $card, array $data): void
    {
        $limits = config('engineering.procurement');
        $orderDate = filled($data['order_date'] ?? null) ? Carbon::parse($data['order_date']) : null;
        $deliveryDate = filled($data['delivery_actual_at'] ?? null) ? Carbon::parse($data['delivery_actual_at']) : null;

        if ($orderDate && $card->approved_at
            && $card->approved_at->copy()->startOfDay()->diffInDays($orderDate) > $limits['approval_to_order_days']
            && blank($data['delay_reason_order'] ?? null)) {
            throw new EngineeringActionException("The order was placed more than {$limits['approval_to_order_days']} days after approval. Give the reason for the delay.");
        }

        if ($orderDate && $deliveryDate
            && $orderDate->diffInDays($deliveryDate) > $limits['order_to_delivery_days']
            && blank($data['delay_reason_delivery'] ?? null)) {
            throw new EngineeringActionException("Delivery took more than {$limits['order_to_delivery_days']} days after the order. Give the reason for the delay.");
        }
    }

    protected function assertEngineerCanEdit(JobCard $card, User $actor): void
    {
        if (! $card->isEditable()) {
            throw new EngineeringActionException('This job card has been submitted and is locked. It can only change if it is returned to you.');
        }

        if (! EngineeringAccess::canWorkJob($actor, $card->job)) {
            throw new EngineeringActionException('Only the assigned engineer or an assistant can edit this job card.');
        }
    }

    /** Separation of duties: never the requesting engineer, and never the same person at both stages. */
    protected function assertMayDecide(JobCard $card, User $actor, string $stage): void
    {
        $allowed = $stage === 'certify' ? EngineeringAccess::canCertify($actor) : EngineeringAccess::canAuthorise($actor);
        if (! $allowed) {
            throw new EngineeringActionException('You are not authorised to '.$stage.' job cards.');
        }

        if (EngineeringAccess::employee($actor)?->id === $card->requesting_engineer_id) {
            throw new EngineeringActionException('The requesting engineer cannot certify or authorise their own job card.');
        }

        if ($stage === 'authorise' && $card->certification()?->actor_id === $actor->id) {
            throw new EngineeringActionException('The job card must be authorised by someone other than the person who certified it.');
        }
    }
}
