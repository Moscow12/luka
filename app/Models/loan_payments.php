<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class loan_payments extends Model
{
    use HasUuids;
    protected $table = 'loan_payments';

    protected $fillable = [
        'loanrequest_id',
        'status',
        'payment_date',
        'amount',
        'remarks',
        'recorded_by',
    ];

    public function loan_request(): BelongsTo
    {
        return $this->belongsTo(loanrequests::class, 'loanrequest_id', 'id');
    }

    public function recorded_by(): BelongsTo
    {
        return $this->belongsTo(employee::class, 'recorded_by', 'id');
    }
}
