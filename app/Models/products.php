<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class products extends Model
{
    use HasUuids, LogsActivity;

    protected $table = 'products';
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'code', 'barcode', 'description', 'product_category_id',
                'cost_price', 'selling_price', 'type', 'status', 'unit',
                'track_stock', 'is_serialized', 'requires_approval',
                'reorder_level', 'useful_life'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Product has been {$eventName}")
            ->useLogName('product');
    }

    public function category()
    {
        return $this->belongsTo(productcategory::class, 'product_category_id');
    }
}
