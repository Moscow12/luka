<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class districts extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'districts';
    protected $fillable = [
        'name',
        'region_id',
    ];

    public function regions()
    {
        return $this->belongsTo(regions::class, 'region_id');
    }

    public function wards()
    {
        return $this->hasMany(wards::class, 'district_id');
    }
}
