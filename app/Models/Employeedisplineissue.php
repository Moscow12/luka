<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employeedisplineissue extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeedisplineissueFactory> */
    use HasFactory, HasUuids, SoftDeletes;
    //violation_id, employee_id, violation_date, notes, attachment, added_by
    protected $table = 'employeedisplineissues';
    protected $fillable = [
        'violation_id',
        'employee_id',
        'violation_date',
        'notes',
        'attachment',
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

    public function violation()
    {
        return $this->belongsTo(Violations::class, 'violation_id');
    }
}
