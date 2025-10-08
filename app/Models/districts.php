<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class districts extends Model
{
    protected $table = 'districts';
    protected $fillable = [
        'name',
        'state_id',
    ];

    public function state()
    {
        return $this->belongsTo(regions::class, 'state_id');
    }
}
