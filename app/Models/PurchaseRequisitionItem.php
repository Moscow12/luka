<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_requisition_id',
        'store_order_item_id',
        'store_order_id',
        'supplier_id',
        'price',
        'quantity',
        'quotation1',
        'quotation2',
        'quotation3',
        'status',
        'remarks',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_ACTIVE = 'active';

    const STATUS_REJECTED = 'rejected';

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function storeOrderItem()
    {
        return $this->belongsTo(StoreOrderItem::class);
    }

    public function storeOrder()
    {
        return $this->belongsTo(StoreOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(vendors::class, 'supplier_id');
    }

    public function localPurchaseOrderItem()
    {
        return $this->hasOne(LocalPurchaseOrderItem::class);
    }

    public function isQuotationComplete(): bool
    {
        return filled($this->supplier_id) && filled($this->price) && filled($this->quotation1);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
