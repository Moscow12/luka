<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class approvalleveltoemployee extends Model
{
    use HasUuids;

    protected $table = 'approvalleveltoemployees';

    protected $fillable = [
        'approval_level_id',
        'employee_id',
        'is_active',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approval_level()
    {
        return $this->belongsTo(approvallevel::class, 'approval_level_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Departments this mapping is scoped to. An empty set means the mapping applies
     * to every department (used for level 3/4 company-wide approvers).
     */
    public function departments()
    {
        return $this->belongsToMany(departments::class, 'approval_mapping_departments', 'mapping_id', 'department_id');
    }

    public function appliesToDepartment(?string $departmentId): bool
    {
        if (! $departmentId) {
            return false;
        }

        if ($this->departments->isEmpty()) {
            return true; // No departments picked = applies company-wide.
        }

        return $this->departments->contains('id', $departmentId);
    }
}
