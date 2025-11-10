<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class fpusers extends Model
{
    use HasUuids;
    protected $table = 'fpusers';
    protected $fillable = [
        'name',
        'fpdevice_id',
        'fpdevice_address',
        'added_by',
    ];
  
    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
