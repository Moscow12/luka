<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employeepromotions extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeepromotionsFactory> */
    use HasFactory, HasUuids, SoftDeletes;

     // title_id, workstation_id, department_id, start_date, attachment, comments, employee_id, added_by
     protected $table = 'employeepromotions';
     protected $fillable = [
        'title_id',
        'workstation_id',
        'department_id',
        'start_date',
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

    public function title()
    {
        return $this->belongsTo(Jobtitle::class, 'title_id');
    }

    public function workstation()
    {
        return $this->belongsTo(Workstations::class, 'workstation_id');
    }

    public function department()
    {
        return $this->belongsTo(departments::class, 'department_id');
    }
}
