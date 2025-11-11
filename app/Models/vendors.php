<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class vendors extends Model
{
    use HasUuids;

    protected $table = 'vendors';

    protected $fillable = [
        'name',
        'vendor_type',
        'vendor_number',
        'email',
        'phone',
        'address',
        'status',
        'contact_person',
        'contact_email',
        'contact_phone',
        'description',
        'country_id',
        'region_id',
        'district_id',
        'ward_id',
        'vilstreet_id',
        'added_by',
    ];

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
        return $this->belongsTo(street::class, 'vilstreet_id');
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
