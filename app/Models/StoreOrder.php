<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_number',
        'department_id',
        'status',
        'order_description',
        'requested_by',
        'approved_by',
        'submitted_at',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';

    const STATUS_SUBMITTED = 'submitted';

    const STATUS_REVIEW = 'review';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    const STATUS_ISSUED = 'issued';

    // Document type key used for approval-level mapping (Setup > Approval Configuration)
    const APPROVAL_DOCUMENT_TYPE = 'Purchase';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_REVIEW => 'Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_ISSUED => 'Issued',
        ];
    }

    // Generate a unique order number
    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;

        return sprintf('SO-%s-%04d', $year, $count);
    }

    // Relationships
    public function department()
    {
        return $this->belongsTo(departments::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(StoreOrderItem::class, 'store_order_id');
    }

    public function assignedDuties()
    {
        return $this->hasMany(EmployeeAssignedDuty::class, 'store_order_id');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeReview($query)
    {
        return $query->where('status', self::STATUS_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // Status checks
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isReview(): bool
    {
        return $this->status === self::STATUS_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canEdit(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canSubmit(): bool
    {
        return $this->status === self::STATUS_DRAFT
            && $this->items()->count() > 0;
    }

    /**
     * True while the order is awaiting the first-tier approval action
     * (submitted -> review).
     */
    public function canFirstApprove(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /**
     * True while the order is awaiting the senior/final approval action
     * (review -> approved).
     */
    public function canFinalApprove(): bool
    {
        return $this->status === self::STATUS_REVIEW;
    }

    /**
     * True while the order can still be actioned by any approval tier
     * (used to gate the reject action, which is allowed at either tier).
     */
    public function canApprove(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_REVIEW], true);
    }
}
