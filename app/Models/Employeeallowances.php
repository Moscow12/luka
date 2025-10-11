<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employeeallowances extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeallowancesFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    //employee_id, allowance_id, allowance_amount, salary_id, added_by
    protected $table = 'employeeallowances';
    protected $fillable = [
        'employee_id',
        'allowance_id',
        'allowance_amount',
        'salary_id',
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

    public function allowance()
    {
        return $this->belongsTo(allowances::class, 'allowance_id');
    }

    public function salary()
    {
        return $this->belongsTo(Employeesalaries::class, 'salary_id');
    }
}
