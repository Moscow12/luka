<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employeesalaries extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeesalariesFactory> */
    use HasFactory, HasUuids;
    protected $table = 'employeesalaries';
    protected $fillable = [
        'employee_id',
        'contract_id',
        'amount',
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

    public function contract()
    {
        return $this->belongsTo(Employeecontracts::class, 'contract_id');
    }

    public function allowances()
    {
        return $this->hasMany(Employeeallowances::class, 'salary_id');
    }

    public function deductions()
    {
        return $this->hasMany(Employeedeductions::class, 'salary_id');
    }

}
