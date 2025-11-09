<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDeduction extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'contract_deductions';
    protected $fillable = [
        'contract_id',
        'deduction_id',
        'amount_override',
        'is_active',
        'description',
        'added_by',
    ];
    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function contract()
    {
        return $this->belongsTo(Employeecontracts::class, 'contract_id');
    }

    public function deduction()
    {
        return $this->belongsTo(Deduction::class, 'deduction_id');
    }
}
