<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityReport extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'activity_id',
        'reported_by',
        'report_month',
        'status',
        'completion_notes',
        'challenges_faced',
        'amount_spent',
        'payment_proof_document',
        'activity_proof_document',
        'actual_completion_date',
        'beneficiaries_reached',
        'outcomes_achieved',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'actual_completion_date' => 'date',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
        'amount_spent' => 'decimal:2',
        'beneficiaries_reached' => 'integer',
    ];

    /**
     * Get the activity that this report belongs to
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(chopactivities::class, 'activity_id');
    }

    /**
     * Get the user who submitted the report
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the user who approved the report
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get available status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'completed' => 'Completed',
            'partially_completed' => 'Partially Completed',
            'not_completed' => 'Not Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Get the formatted month name
     */
    public function getFormattedMonthAttribute(): string
    {
        return \Carbon\Carbon::parse($this->report_month . '-01')->format('F Y');
    }
}
