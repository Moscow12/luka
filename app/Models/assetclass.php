<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class assetclass extends Model
{
    use HasUuids;

    protected $table = 'assetclasses';
    protected $fillable = ['name', 'depreciation', 'depreciation_method', 'useful_life_years', 'depreciation_rate', 'added_by'];

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function assets()
    {
        return $this->hasMany(asset::class, 'asset_class_id');
    }
}
