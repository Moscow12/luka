<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class chopitems extends Model
{
    use HasUuids;

    protected $table = 'chopitems';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'gfc_code',
        'unit',
        'quantity',
        'price',
        'description',
        'added_by',
        'category_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(chopcategoryarea::class, 'category_id');
    }
}
