<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employeeleaves extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeleavesFactory> */
    use HasFactory, HasUuids;

    // leave_id, employee_id, start_date, end_date, days, travel_to, othercontact, comments, added_by
    protected $table = 'employeeleaves';
    protected $fillable = [
        'leave_id',
        'employee_id',
        'start_date',
        'end_date',
        'days',
        'travel_to',
        'othercontact',
        'comments',
        'document',
        'status',
        'added_by',
        'approved_by',
        'approved_at',
        'approval_note',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approved_by_user()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leave()
    {
        return $this->belongsTo(Leaves::class, 'leave_id');
    }

    public function approvalnote()
    {
        return $this->hasMany(leaverequestapproval::class, 'leave_request_id');
    }

    public function actingAssignment()
    {
        return $this->hasOne(ActingAssignment::class, 'leave_request_id');
    }
}
