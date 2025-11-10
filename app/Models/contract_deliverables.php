<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class contract_deliverables extends Model
{
    use HasUuids;

    protected $table = 'contract_deliverables';

    protected $fillable = [
        'contract_id',
        'deliverable_name',
        'deliverable_number',
        'deliverable_type',
        'description',
        'deliverable',
        'kpi',
        'status',
        'due_date',
        'remarks',
        'added_by',
    ];
    
    public function contract()
    {
        return $this->belongsTo(contracts::class, 'contract_id');
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
