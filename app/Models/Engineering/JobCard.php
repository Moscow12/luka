<?php

namespace App\Models\Engineering;

use App\Models\Employee;
use App\Models\Engineering\Concerns\LogsEngineeringActivity;
use App\Models\LocalPurchaseOrder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The costed, authorised document that turns "this needs a spare" into a purchase.
 */
class JobCard extends EngineeringModel
{
    use LogsEngineeringActivity;

    protected $table = 'eng_job_cards';

    protected $casts = [
        'date_part_requested' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'order_date' => 'date',
        'proforma_requested_at' => 'date',
        'proforma_received_at' => 'date',
        'spare_procured_at' => 'date',
        'tender_opened_at' => 'date',
        'tender_approved_at' => 'date',
        'delivery_expected_at' => 'date',
        'delivery_actual_at' => 'date',
        'labour_charge' => 'float',
        'labour_qty' => 'float',
        'fabrication_charge' => 'float',
        'fabrication_qty' => 'float',
        'sub_total' => 'float',
        'grand_total' => 'float',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(JobCardItem::class, 'job_card_id')->orderBy('seq');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(JobCardApproval::class, 'job_card_id')->orderBy('created_at');
    }

    public function requestingEngineer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requesting_engineer_id');
    }

    public function localPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(LocalPurchaseOrder::class, 'local_purchase_order_id');
    }

    public function certification(): ?JobCardApproval
    {
        return $this->approvalFor('certify');
    }

    public function authorisation(): ?JobCardApproval
    {
        return $this->approvalFor('authorise');
    }

    /** The approving decision of a stage for the current revision. */
    protected function approvalFor(string $stage): ?JobCardApproval
    {
        return $this->approvals
            ->where('stage', $stage)
            ->where('decision', 'approved')
            ->where('revision', $this->revision)
            ->last();
    }

    /* ---------- State ---------- */

    /** The engineer may still change lines and costs. */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'returned'], true);
    }

    /** Authorised: now with the purchasing office. */
    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'purchasing', 'delivered', 'closed'], true);
    }

    /* ---------- Costing: always computed, never typed ---------- */

    public function totalLabour(): float
    {
        return round($this->labour_charge * $this->labour_qty, 2);
    }

    public function totalFabrication(): float
    {
        return round($this->fabrication_charge * $this->fabrication_qty, 2);
    }

    public function recalculate(): void
    {
        $subTotal = $this->items()->get()->sum(fn (JobCardItem $item) => $item->total());

        $this->forceFill([
            'sub_total' => $subTotal,
            'grand_total' => $subTotal + $this->totalLabour() + $this->totalFabrication(),
        ])->save();
    }

    /* ---------- Procurement tracking ---------- */

    public function daysSinceSubmitted(): ?int
    {
        return $this->submitted_at ? (int) $this->submitted_at->diffInDays(now()) : null;
    }

    public function daysApprovalToOrder(): ?int
    {
        return $this->approved_at && $this->order_date
            ? (int) $this->approved_at->copy()->startOfDay()->diffInDays($this->order_date)
            : null;
    }

    public function daysOrderToDelivery(): ?int
    {
        return $this->order_date && $this->delivery_actual_at
            ? (int) $this->order_date->diffInDays($this->delivery_actual_at)
            : null;
    }

    /** Order to actual arrival, or order to today while the delivery is outstanding. */
    public function deliveryPeriod(): ?int
    {
        return $this->order_date
            ? (int) $this->order_date->diffInDays($this->delivery_actual_at ?? now()->startOfDay())
            : null;
    }

    /** complete | delayed | on_track, derived purely from the dates. */
    public function procurementStatus(): string
    {
        if ($this->delivery_actual_at) {
            return 'complete';
        }

        $today = now()->startOfDay();
        $limits = config('engineering.procurement');

        $pastExpectedArrival = $this->delivery_expected_at && $today->gt($this->delivery_expected_at);
        $slowToOrder = $this->approved_at
            && $this->approved_at->copy()->startOfDay()->diffInDays($this->order_date ?? $today) > $limits['approval_to_order_days'];
        $slowToDeliver = $this->order_date && $this->order_date->diffInDays($today) > $limits['order_to_delivery_days'];

        return $pastExpectedArrival || $slowToOrder || $slowToDeliver ? 'delayed' : 'on_track';
    }
}
