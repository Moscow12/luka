<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ActingAssignment extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'leave_request_id',
        'employee_on_leave_id',
        'acting_employee_id',
        'acting_designation_id',
        'acting_department_id',
        'start_date',
        'end_date',
        'responsibilities',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notify_acting_employee',
        'grant_system_access',
        'added_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'notify_acting_employee' => 'boolean',
        'grant_system_access' => 'boolean',
    ];

    // Activity Logging
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'acting_employee_id', 'start_date', 'end_date'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Acting assignment {$eventName}")
            ->useLogName('acting_assignment');
    }

    /* ====================
     | RELATIONSHIPS
     ==================== */

    public function leaveRequest()
    {
        return $this->belongsTo(Employeeleaves::class, 'leave_request_id');
    }

    public function employeeOnLeave()
    {
        return $this->belongsTo(Employee::class, 'employee_on_leave_id');
    }

    public function actingEmployee()
    {
        return $this->belongsTo(Employee::class, 'acting_employee_id');
    }

    public function actingDesignation()
    {
        return $this->belongsTo(designations::class, 'acting_designation_id');
    }

    public function actingDepartment()
    {
        return $this->belongsTo(departments::class, 'acting_department_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /* ====================
     | SCOPES
     ==================== */

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('acting_employee_id', $employeeId);
    }

    /* ====================
     | ACCESSORS & MUTATORS
     ==================== */

    public function getDurationInDaysAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active'
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    public function getIsExpiredAttribute()
    {
        return $this->end_date < now();
    }

    /* ====================
     | METHODS
     ==================== */

    public function approve($approvedBy)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    public function reject($rejectedBy, $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function activate()
    {
        if ($this->status === 'approved' && $this->start_date <= now()) {
            $this->update(['status' => 'active']);
        }
    }

    public function complete()
    {
        if ($this->status === 'active' && $this->end_date < now()) {
            $this->update(['status' => 'completed']);
        }
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
