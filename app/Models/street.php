<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class street extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'streets';
    protected $fillable = [
        'name',
        'street_number',
        'ward_id',
    ];

    public function ward()
    {
        return $this->belongsTo(wards::class, 'ward_id');
    }

}
