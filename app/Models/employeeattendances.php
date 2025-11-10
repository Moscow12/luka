<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class employeeattendances extends Model
{
    use HasUuids;

    protected $table = 'employeeattendances';

    protected $fillable = [
        'fpuser_id',
        'clockdate',
        'device_id',
        'clocktimestamp',
        'status',
        'clocktime',
        'clock_status',
        'clock_in',
        'clock_out',
    ];

    public function fpuser()
    {
        return $this->belongsTo(fpusers::class, 'fpuser_id', 'fpdevice_id');
    }
}
