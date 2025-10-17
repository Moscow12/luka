<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class regions extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'regions';
    protected $fillable = [
        'name',
        'country_id',
    ];

    public function country()
    {
        return $this->belongsTo(countries::class, 'country_id');
    }

    public function districts()
    {
        return $this->hasMany(districts::class, 'region_id');
    }
}
