<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LocalPurchaseOrderItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'local_purchase_order_id',
        'purchase_requisition_item_id',
        'item_name',
        'supplier_id',
        'supplier_name',
        'price',
        'quantity',
        'quotation1',
        'quotation2',
        'quotation3',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function localPurchaseOrder()
    {
        return $this->belongsTo(LocalPurchaseOrder::class);
    }

    public function purchaseRequisitionItem()
    {
        return $this->belongsTo(PurchaseRequisitionItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(vendors::class, 'supplier_id');
    }
}
