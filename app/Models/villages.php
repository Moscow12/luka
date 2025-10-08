<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class villages extends Model
{
    protected $table = 'villages';
    protected $fillable = [
        'name',
        'town_id',
    ];

    public function town()
    {
        return $this->belongsTo(wards::class, 'town_id');
    }
}
