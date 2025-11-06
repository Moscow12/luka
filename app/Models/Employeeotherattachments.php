<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Employeeotherattachments extends Model
{
    use HasUuids;
    protected $table = 'employeeotherattachments';
    protected $fillable = [
        'type',
        'attachment',
        'description',
        'employee_id',
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
