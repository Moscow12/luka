<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class payrolls extends Model
{
    use HasUuids;
    protected $table = 'payrolls';
    protected $fillable = [
        'employee_id',
        'contract_id',
        'period',
        'basic_salary',
        'gross_salary',
        'taxable_salary',
        'mafao_deductions',
        'paye_tax',
        'total_allowances',
        'total_deductions',
        'net_salary',
        'status',
        'processed_at',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function contract()
    {
        return $this->belongsTo(Employeecontracts::class, 'contract_id');
    }

    public function items()
    {
        return $this->hasMany(payroll_items::class, 'payroll_id');
    }


    public function totalAllowances()
    {
        return $this->items()->where('type', 'allowance')->sum('amount');
    }

    public function totalDeductions()
    {
        return $this->items()->where('type', 'deduction')->sum('amount');
    }
}
