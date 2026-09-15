<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employeeattachment extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeattachmentFactory> */
    use HasFactory, SoftDeletes, HasUuids;
    //employee_id, notes, attachment, added_by
    protected $table = 'employeeattachments';
    protected $fillable = [
        'employee_id',
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
}
