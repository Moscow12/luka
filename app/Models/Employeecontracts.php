<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employeecontracts extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employeecontracts';

    protected $fillable = [
        'employee_id',
        'workstation_id',
        'department_id',
        'position_id',
        'contract_type',
        'status',
        'start_date',
        'expire_date',
        'expirenotification',
        'notify_time',
        'payment_frequency',
        'base_salary',
        'attachment',
        'description',
        'termination_reason',
        'termination_date',
        'termination_notes',
        'added_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expire_date' => 'date',
        'termination_date' => 'date',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function workstation()
    {
        return $this->belongsTo(workstations::class, 'workstation_id');
    }

    public function department()
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(Jobtitle::class, 'position_id');
    }

    public function contractAllowances()
    {
        return $this->hasMany(ContractAllowance::class, 'contract_id');
    }

    public function contractDeductions()
    {
        return $this->hasMany(ContractDeduction::class, 'contract_id');
    }

    /**
     * Check if contract is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || now()->greaterThan($this->expire_date);
    }

    /**
     * Check if contract is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if contract is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }

    /**
     * Check if contract is terminated
     */
    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    /**
     * Check if contract is about to expire (based on expirenotification days)
     */
    public function isAboutToExpire(): bool
    {
        if ($this->isExpired() || $this->isSuspended()) {
            return false;
        }

        $notificationDays = (int) $this->expirenotification;
        $notificationDate = now()->addDays($notificationDays);

        return $notificationDate->greaterThanOrEqualTo($this->expire_date);
    }

    /**
     * Get contract status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => $this->isAboutToExpire() ? 'bg-warning' : 'bg-success',
            'expired' => 'bg-danger',
            'suspended' => 'bg-secondary',
            'terminated' => 'bg-dark',
            default => 'bg-primary'
        };
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabel(): string
    {
        if ($this->isExpired() && $this->status !== 'expired') {
            return 'Expired';
        }

        if ($this->isActive() && $this->isAboutToExpire()) {
            return 'Expiring Soon';
        }

        if ($this->isTerminated() && $this->termination_reason) {
            return ucfirst(str_replace('_', ' ', $this->termination_reason));
        }

        return ucfirst($this->status);
    }

    /**
     * Get formatted termination reason label
     */
    public function getTerminationReasonLabel(): string
    {
        return match ($this->termination_reason) {
            'contract_ended' => 'Contract Ended',
            'resigned' => 'Resigned',
            'terminated' => 'Terminated',
            'deceased' => 'Deceased',
            'transferred' => 'Transferred',
            'retired' => 'Retired',
            'study_leave' => 'Study Leave',
            'absconded' => 'Absconded',
            'other' => 'Other',
            default => 'N/A'
        };
    }
}
