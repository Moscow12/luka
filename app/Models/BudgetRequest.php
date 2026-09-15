<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BudgetRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'request_number',
        'department_id',
        'financial_year_id',
        'status',
        'total_estimated_amount',
        'approved_amount',
        'justification',
        'director_notes',
        'requested_by',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'total_estimated_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';

    const STATUS_SUBMITTED = 'submitted';

    const STATUS_UNDER_REVIEW = 'under_review';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    const STATUS_REVISION_REQUIRED = 'revision_required';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_REVISION_REQUIRED => 'Revision Required',
        ];
    }

    // Generate unique request number
    public static function generateRequestNumber($financialYearId): string
    {
        $year = FinancialYear::find($financialYearId)?->start_date->format('Y') ?? date('Y');
        $count = self::where('financial_year_id', $financialYearId)->count() + 1;

        return sprintf('BR-%s-%04d', $year, $count);
    }

    // Calculate total from items
    public function calculateTotal(): void
    {
        $this->total_estimated_amount = $this->items()->sum('requested_total');
        $this->save();
    }

    // Relationships
    public function department()
    {
        return $this->belongsTo(departments::class);
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items()
    {
        return $this->hasMany(BudgetRequestItem::class, 'budget_request_id');
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

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW]);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
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

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION_REQUIRED]);
    }

    public function canSubmit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION_REQUIRED])
            && $this->items()->count() > 0;
    }
}
