<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class denominations extends Model
{
    protected $table = 'denominations';
    protected $fillable = [
        'name',
        'region_id',
    ];

    public function religion()
    {
        return $this->belongsTo(religions::class, 'religion_id');
    }
}
