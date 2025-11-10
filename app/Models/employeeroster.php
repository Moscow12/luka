<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class employeeroster extends Model
{
    use HasUuids;
    
    protected $table = 'employeerosters';
    protected $fillable = [
        'employee_id',
        'added_by',
        'department_id',
        'roster_date',
        'shift_id',
        'shift_type',
        'status',
        'notes',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function shift()
    {
        return $this->belongsTo(shifts::class, 'shift_id');
    }

    public function department()
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
