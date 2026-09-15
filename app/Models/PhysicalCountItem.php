<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PhysicalCountItem extends Model
{
    use HasUuids;

    protected $table = 'physical_count_items';

    protected $fillable = [
        'physical_count_id',
        'item_id',
        'system_qty',
        'counted_qty',
        'remarks',
    ];

    protected $casts = [
        'system_qty' => 'integer',
        'counted_qty' => 'integer',
    ];

    public function physicalCount()
    {
        return $this->belongsTo(PhysicalCount::class, 'physical_count_id');
    }

    public function item()
    {
        return $this->belongsTo(products::class, 'item_id');
    }

    public function getVarianceAttribute()
    {
        if (is_null($this->counted_qty)) {
            return null;
        }

        return $this->counted_qty - $this->system_qty;
    }
}
