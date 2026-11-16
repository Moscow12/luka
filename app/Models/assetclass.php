<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class assetclass extends Model
{
    use HasActiveStatus, HasUuids, SoftDeletes;

    protected $table = 'assetclasses';

    protected $fillable = ['name', 'depreciation', 'depreciation_method', 'useful_life_years', 'depreciation_rate', 'eng_section_id', 'status', 'added_by'];

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function assets()
    {
        return $this->hasMany(asset::class, 'asset_class_id');
    }

    /** Engineering section that maintains items of this class; null when engineering does not. */
    public function engSection()
    {
        return $this->belongsTo(Engineering\Section::class, 'eng_section_id');
    }

    public function scopeMaintainedByEngineering($query)
    {
        return $query->whereNotNull('eng_section_id');
    }
}
