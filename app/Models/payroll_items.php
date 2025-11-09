<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class payroll_items extends Model
{
    use HasUuids;
    protected $table = 'payroll_items';

    protected $primaryKey = 'id';

    protected $fillable = [
        'payroll_id',
        'contract_allowance_id',
        'contract_deduction_id',
        'name',
        'type',
        'amount',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function payroll()
    {
        return $this->belongsTo(payrolls::class, 'payroll_id');
    }

    public function contractAllowance()
    {
        return $this->belongsTo(ContractAllowance::class, 'contract_allowance_id');
    }

    public function contractDeduction()
    {
        return $this->belongsTo(ContractDeduction::class, 'contract_deduction_id');
    }
}
