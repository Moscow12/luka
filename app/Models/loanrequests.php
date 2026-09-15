<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class loanrequests extends Model
{
    use HasUuids;
    protected $table = 'loanrequests';

    protected $fillable = [
        'employee_id',
        'amount',
        'status',
        'request_date',
        'interest_rate',
        'interest_type',
        'repayment_period_months',
        'reason',
        'remarks',
        'installment_amount',
        'approved_amount',
        'approval_date',
        'loan_item_id',
        'added_by',
        'applicant_type',
    ];

    public function loan_item(): HasOne
    {
        return $this->hasOne(loan_items::class, 'id', 'loan_item_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
