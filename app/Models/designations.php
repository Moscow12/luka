<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class designations extends Model
{
    protected $table = 'designations';
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
