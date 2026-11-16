<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class designations extends Model
{
    use HasActiveStatus, HasFactory, HasUuids;

    // This table stores capitalised status values
    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    protected $table = 'designations';

    protected $fillable = [
        'name',
        'code',
        'status',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    // Relationship with employees
    public function employees()
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }
}
