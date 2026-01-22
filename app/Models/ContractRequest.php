<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'employee_id',
        'contract_id',
        'request_type',
        'proposed_start_date',
        'proposed_end_date',
        'extension_period',
        'reason',
        'last_working_day',
        'handover_notes',
        'employee_attachment',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_comments',
        'hr_confirmation_letter',
        'new_contract_id',
    ];

    protected $casts = [
        'proposed_start_date' => 'date',
        'proposed_end_date' => 'date',
        'last_working_day' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the request.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the contract associated with the request.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Employeecontracts::class, 'contract_id');
    }

    /**
     * Get the user who reviewed the request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the new contract created from this request (if approved).
     */
    public function newContract(): BelongsTo
    {
        return $this->belongsTo(Employeecontracts::class, 'new_contract_id');
    }

    /**
     * Check if request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get request type badge class for UI.
     */
    public function getTypeBadgeClass(): string
    {
        return match ($this->request_type) {
            'renewal' => 'bg-primary',
            'extension' => 'bg-info',
            'termination' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get human readable request type.
     */
    public function getTypeLabel(): string
    {
        return ucfirst($this->request_type);
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for specific request type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('request_type', $type);
    }

    /**
     * Check if this is an early termination (contract not in notification period).
     * Notification period is typically 90 days before expiry.
     */
    public function isEarlyTermination(): bool
    {
        if ($this->request_type !== 'termination' || ! $this->contract) {
            return false;
        }

        $daysUntilExpiry = now()->diffInDays($this->contract->expire_date, false);

        // If more than 90 days until contract expires, it's early termination
        return $daysUntilExpiry > 90;
    }

    /**
     * Check if confirmation letter is required for this request.
     */
    public function requiresConfirmationLetter(): bool
    {
        return $this->request_type === 'termination' && $this->isEarlyTermination();
    }
}
