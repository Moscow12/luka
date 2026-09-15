<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wards extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'wards';
    protected $fillable = [
        'name',
        'district_id',
    ];

    public function districts()
    {
        return $this->belongsTo(districts::class, 'district_id');
    }

    public function streets()
    {
        return $this->hasMany(street::class, 'ward_id');
    }
}
