<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StoreOrderItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'store_order_id',
        'item_id',
        'quantity',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    // Relationships
    public function storeOrder()
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }

    public function item()
    {
        return $this->belongsTo(chopitems::class, 'item_id');
    }
}
