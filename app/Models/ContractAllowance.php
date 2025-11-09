<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAllowance extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'contract_allowances';
    protected $fillable = [
        'contract_id',
        'allowance_id',
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

    public function allowance()
    {
        return $this->belongsTo(allowances::class, 'allowance_id');
    }
}
