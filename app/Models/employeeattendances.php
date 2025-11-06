<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class employeeattendances extends Model
{
    protected $table = 'employeeattendances';
    protected $fillable = [
        'employee_id',
        'clockdate',
        'device_id',
        'clocktimestamp',
        'status',
        'clocktime',
        'clock_status',
        'clock_in',
        'clock_out',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
