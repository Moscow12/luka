<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class chopitems extends Model
{
    protected $table = 'chopitems';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

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

    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(chopcategoryarea::class);
    }   
}
