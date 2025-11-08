<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employeedeductions extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeedeductionsFactory> */
    use HasFactory, SoftDeletes, HasUuids;
    protected $table = 'employeedeductions';
    protected $fillable = [
        'employee_id',
        'deductions_id',
        'deductions_amount',
        'salary_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function deduction()
    {
        return $this->belongsTo(Deduction::class, 'deductions_id');
    }

    public function salary()
    {
        return $this->belongsTo(Employeesalaries::class, 'salary_id');
    }
}
