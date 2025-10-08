<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class workstations extends Model
{
    protected $table = 'workstations';
    protected $fillable = [
        'name',
        'location',
        'phone_number',
        'tin_number',
        'email_address',
        'address',
        'city',
        'province',
        'country',
        'postal_code',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
