<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class loan_approval extends Model
{
    use HasUuids;
    protected $table = 'loan_approvals';

    protected $fillable = [
        'loanrequest_id',
        'approved_by',
        'status',
        'approval_date',
        'approved_amount',
        'remarks',
    ];

    public function loan_request(): BelongsTo
    {
        return $this->belongsTo(loanrequests::class, 'loanrequest_id', 'id');
    }

    public function approved_by(): BelongsTo
    {
        return $this->belongsTo(employee::class, 'approved_by', 'id');
    }
}
