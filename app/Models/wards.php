<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class wards extends Model
{
    protected $table = 'wards';
    protected $fillable = [
        'name',
        'district_id',
    ];

    public function district()
    {
        return $this->belongsTo(districts::class, 'district_id');
    }
}
