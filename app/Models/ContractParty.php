<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContractParty extends Model
{
    use HasUuids;

    protected $table = 'contract_parties';

    protected $fillable = [
        'contract_id',
        'party_type',
        'party_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'registration_number',
        'status',
        'notes',
        'added_by',
    ];

    public function contract()
    {
        return $this->belongsTo(contracts::class, 'contract_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
