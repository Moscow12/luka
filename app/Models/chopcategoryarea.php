<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class chopcategoryarea extends Model
{
    use HasUuids;

    protected $table = 'chopcategoryareas';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'added_by',
    ];

    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function chopItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(chopitems::class, 'category_id');
    }
}
