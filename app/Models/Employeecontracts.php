<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employeecontracts extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'employeecontracts';
    protected $fillable = [
        'employee_id',
        'workstation_id',
        'position_id',
        'contract_type',
        'start_date',
        'expire_date',
        'expirenotification',
        'notify_time',
        'attachment',
        'description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function workstation()
    {
        return $this->belongsTo(Workstations::class, 'workstation_id');
    }

    public function position()
    {
        return $this->belongsTo(Jobtitle::class, 'position_id');
    }
}
