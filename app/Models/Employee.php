<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    use LogsActivity, SoftDeletes, HasFactory, HasUuids;
    protected $fillable = [
        'user_id',
        'employee_no',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'national_id',
        'phone',
        'email',
        'employment_type',
        'hired_date',
        'status',
        'department_id',
        'education_level',
        'job_title_id',
        'fpid',
        'photo',
        'marital_status',
        'status',
        'country_id',
        'region_id',
        'district_id',
        'ward_id',
        'vilstreet_id',
        'tin_number',
        'designation_id',
        'workstation_id',
        'denomination_id',
        'added_by',
    ];

    

    protected $casts = [
        'dob' => 'date',
        'hired_date' => 'date',
    ];

    // Activity Logging Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'employment_status', 'department_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Employee {$eventName}")
            ->useLogName('employee')
            ->dontLogIfAttributesChangedOnly(['updated_at']);
    }

    /* ====================
     | RELATIONSHIPS
     ==================== */

    public function user()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function department()
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(Jobtitle::class, 'title_id');
    }

    public function designation()
    {
        return $this->belongsTo(designations::class, 'designation_id');
    }

    public function country()
    {
        return $this->belongsTo(countries::class, 'country_id');
    }

    public function region()
    {
        return $this->belongsTo(regions::class, 'region_id');
    }

    public function district()
    {
        return $this->belongsTo(districts::class, 'district_id');
    }

    public function ward()
    {
        return $this->belongsTo(wards::class, 'ward_id');
    }

    public function vilstreet()
    {
        return $this->belongsTo(villages::class, 'vilstreet_id');
    }

    public function workstation()
    {
        return $this->belongsTo(Workstations::class, 'workstation_id');
    }

    public function denomination()
    {
        return $this->belongsTo(denominations::class, 'denomination_id');
    }
    #================ end relationships ================#

    #================ COMPUTED ATTRIBUTES ================#
    // Computed Attributes
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getYearsOfServiceAttribute()
    {
        return $this->hired_date->diffInYears(now());
    }

    public function getAgeAttribute()
    {
        return $this->dob->diffInYears(now());
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }
    #================ END computed attributes ================#
}