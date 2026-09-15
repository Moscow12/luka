<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PhysicalCount extends Model
{
    use HasUuids;

    protected $table = 'physical_counts';

    protected $fillable = [
        'department_store_id',
        'count_date',
        'status',
        'remarks',
        'added_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'count_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function departmentStore()
    {
        return $this->belongsTo(DepartmentStore::class, 'department_store_id');
    }

    public function items()
    {
        return $this->hasMany(PhysicalCountItem::class, 'physical_count_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
