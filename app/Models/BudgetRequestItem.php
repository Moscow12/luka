<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BudgetRequestItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'budget_request_id',
        'item_id',
        'category_id',
        'requested_quantity',
        'requested_price',
        'justification',
        'approved_quantity',
        'approved_price',
        'director_comment',
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:2',
        'requested_price' => 'decimal:2',
        'approved_quantity' => 'decimal:2',
        'approved_price' => 'decimal:2',
        'requested_total' => 'decimal:2',
        'approved_total' => 'decimal:2',
    ];

    // Relationships
    public function budgetRequest()
    {
        return $this->belongsTo(BudgetRequest::class, 'budget_request_id');
    }

    public function item()
    {
        return $this->belongsTo(chopitems::class, 'item_id');
    }

    public function category()
    {
        return $this->belongsTo(chopcategoryarea::class, 'category_id');
    }

    // Calculate totals
    public function getRequestedTotalAttribute(): float
    {
        return $this->requested_quantity * ($this->requested_price ?? 0);
    }

    public function getApprovedTotalAttribute(): float
    {
        return ($this->approved_quantity ?? 0) * ($this->approved_price ?? 0);
    }
}
