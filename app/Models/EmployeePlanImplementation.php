<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePlanImplementation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'employee_plan_item_id',
        'employee_id',
        'implementation_date',
        'activity_title',
        'activity_description',
        'quantity_achieved',
        'unit',
        'evidence',
        'challenges',
        'lessons_learned',
        'status',
        'supervisor_remarks',
        'verified_by',
        'verified_at',
        'added_by',
    ];

    protected $casts = [
        'implementation_date' => 'date',
        'quantity_achieved' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function employeePlanItem(): BelongsTo
    {
        return $this->belongsTo(EmployeePlanItem::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
