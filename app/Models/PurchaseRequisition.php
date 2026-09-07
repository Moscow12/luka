<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    use HasUuids;

    protected $fillable = [
        'requisition_number',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';

    const STATUS_APPROVED = 'approved';

    public static function generateRequisitionNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;

        return sprintf('PR-%s-%04d', $year, $count);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function localPurchaseOrder()
    {
        return $this->hasOne(LocalPurchaseOrder::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canApprove(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canFillQuotations(): bool
    {
        return $this->isApproved() && ! $this->localPurchaseOrder()->exists();
    }

    public function canGenerateLpo(): bool
    {
        if (! $this->isApproved() || $this->localPurchaseOrder()->exists()) {
            return false;
        }

        $approvedItems = $this->items->where('status', PurchaseRequisitionItem::STATUS_APPROVED);

        if ($approvedItems->isEmpty()) {
            return false;
        }

        return $approvedItems->every(fn (PurchaseRequisitionItem $item) => $item->isQuotationComplete());
    }
}
