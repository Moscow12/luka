<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Testing\Fluent\Concerns\Has;

class loan_items extends Model
{
    use HasUuids;

    protected $table = 'loan_items';

    protected $fillable = [
        'name',
        'min_amount',
        'max_amount',
        'interest_rate',
        'repayment_period_months',
        'added_by',
        'workstation_id',
    ];

    public function workstation(): HasOne
    {
        return $this->hasOne(workstations::class, 'id', 'workstation_id');
    }
}
