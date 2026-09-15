<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Employeedependants extends Model
{
    use HasUuids;
    protected $table = 'employeedependants';
    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'occupation',
        'address',
        'is_next_of_kin',
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
