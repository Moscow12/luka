<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class contract_approvals extends Model
{
    use HasUuids;

    protected $table = 'contract_approvals';

    protected $fillable = [
        'contract_id',
        'approver_id',
        'stage',
        'approver_name',
        'status',
        'comments',
    ];

    public function contract()
    {
        return $this->belongsTo(contracts::class, 'contract_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
