<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class violations extends Model
{
    protected $table = 'violations';
    protected $fillable = [
        'name',
        'description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
