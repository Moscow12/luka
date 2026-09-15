<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class asset extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'assets';

    protected $fillable = ['name', 'type', 'asset_class_id', 'added_by'];

    public function asset_class()
    {
        return $this->belongsTo(assetclass::class, 'asset_class_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
