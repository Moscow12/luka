<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LocalPurchaseOrder extends Model
{
    use HasUuids;

    protected $fillable = [
        'lpo_number',
        'purchase_requisition_id',
        'generated_by',
        'generated_at',
        'total_amount',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public static function generateLpoNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;

        return sprintf('LPO-%s-%04d', $year, $count);
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function items()
    {
        return $this->hasMany(LocalPurchaseOrderItem::class);
    }
}
