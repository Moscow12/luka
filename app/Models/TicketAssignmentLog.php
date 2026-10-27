<?php

namespace App\Models;

use App\Models\Concerns\WriteOnce;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAssignmentLog extends Model
{
    use HasUuids, WriteOnce;

    const UPDATED_AT = null;

    protected $fillable = [
        'ticket_id',
        'previous_employee_id',
        'new_employee_id',
        'previous_department_id',
        'new_department_id',
        'acted_by',
        'reason',
    ];

    public function previousEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'previous_employee_id');
    }

    public function newEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'new_employee_id');
    }

    public function previousDepartment(): BelongsTo
    {
        return $this->belongsTo(departments::class, 'previous_department_id');
    }

    public function newDepartment(): BelongsTo
    {
        return $this->belongsTo(departments::class, 'new_department_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
