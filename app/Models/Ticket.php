<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'ticket_number',
        'subject',
        'description',
        'category_id',
        'priority',
        'department_id',
        'assignee_employee_id',
        'employee_assigned_duty_id',
        'due_date',
        'visibility',
        'status',
        'created_by',
        'accepted_at',
        'started_at',
        'resolved_at',
        'closed_at',
        'closed_by',
        'reopen_count',
        'quality_rating',
        'closing_remarks',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'due_date' => 'date',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'reopen_count' => 'integer',
        'quality_rating' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ticket')
            ->logOnly(['status', 'priority', 'due_date', 'assignee_employee_id', 'department_id', 'quality_rating'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assignee_employee_id');
    }

    public function duty(): BelongsTo
    {
        return $this->belongsTo(EmployeeAssignedDuty::class, 'employee_assigned_duty_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TicketParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function latestPublicMessage(): HasOne
    {
        return $this->hasOne(TicketMessage::class)->where('type', 'public')->latestOfMany();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function assignmentLogs(): HasMany
    {
        return $this->hasMany(TicketAssignmentLog::class)->orderBy('created_at');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(TicketStatusLog::class)->orderBy('created_at');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->endOfDay()->isPast()
            && ! in_array($this->status, [TicketStatus::Resolved, TicketStatus::Closed, TicketStatus::Cancelled], true);
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now()->toDateString())
            ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value, TicketStatus::Cancelled->value]);
    }

    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            'critical' => 'bg-danger',
            'high' => 'bg-warning text-dark',
            'medium' => 'bg-info',
            default => 'bg-secondary',
        };
    }
}
