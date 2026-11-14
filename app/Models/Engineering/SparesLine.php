<?php

namespace App\Models\Engineering;

use App\Models\products;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparesLine extends EngineeringModel
{
    protected $table = 'eng_spares_lines';

    protected $casts = [
        'qty_requested' => 'float',
        'qty_issued' => 'float',
        'date_consumed' => 'date',
        'issued_at' => 'datetime',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(SparesRequisition::class, 'spares_requisition_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
