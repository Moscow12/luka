<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employeequalifications extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeequalificationsFactory> */
    use HasFactory, SoftDeletes, HasUuids;
    //Eductation_level, Institution, start_date, end_date, attachment, comments, employee_id, added_by
    protected $table = 'employeequalifications';
    protected $fillable = [
        'education_level',
        'institution',
        'start_date',
        'end_date',
        'attachment',
        'comments',
        'employee_id',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
