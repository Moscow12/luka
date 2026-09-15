<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class contract_renewals extends Model
{
    use HasUuids;

    protected $table = 'contract_renewals';

    protected $fillable = [
        'contract_id',
        'old_end_date',
        'new_end_date',
        'new_value',
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
