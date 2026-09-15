<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockLedgerControl extends Model
{
    use HasUuids;

    protected $table = 'stock_ledger_controls';

    protected $fillable = [
        'item_id',
        'department_store_id',
        'internal_source',
        'external_source',
        'document_number',
        'pre_balance',
        'post_balance',
        'movement_type',
        'movement_date',
        'added_by',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'pre_balance' => 'integer',
        'post_balance' => 'integer',
        'qty' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(products::class, 'item_id');
    }

    public function departmentStore()
    {
        return $this->belongsTo(DepartmentStore::class, 'department_store_id');
    }

    public function internalSource()
    {
        return $this->belongsTo(DepartmentStore::class, 'internal_source');
    }

    public function externalSource()
    {
        return $this->belongsTo(DepartmentStore::class, 'external_source');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
