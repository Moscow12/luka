<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DepartmentStore extends Model
{
    use HasUuids;

    protected $table = 'department_stores';

    protected $fillable = [
        'department_id',
        'location_id',
        'name',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function location()
    {
        return $this->belongsTo(facilitylocation::class, 'location_id');
    }
}
