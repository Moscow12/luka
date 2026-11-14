<?php

namespace App\Models\Engineering;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends EngineeringModel
{
    protected $table = 'eng_sections';

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'eng_section_staff', 'section_id', 'employee_id')
            ->using(SectionStaff::class)
            ->withPivot('is_head')
            ->withTimestamps();
    }

    /** Staff who may be given work: frozen (non-active) accounts are left out. */
    public function assignableStaff(): BelongsToMany
    {
        return $this->staff()->where('employees.status', 'Active')->orderBy('first_name')->orderBy('last_name');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ProblemCategory::class, 'section_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->orderBy('sort_order')->orderBy('name');
    }
}
