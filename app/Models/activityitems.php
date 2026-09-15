<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class activityitems extends Model
{
    use HasUuids;
    protected $table = 'activityitems';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'activity_id',
        'item_id',
        'quantity',
        'price',
        'added_by',
    ];
    
    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(chopactivities::class);
    }

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(chopitems::class);
    }
}
