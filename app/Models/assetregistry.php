<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class assetregistry extends Model
{
    use HasUuids;

    protected $table = 'assetregistries';

    protected $fillable = [
        'asset_class_id',
        'facility_location_id',
        'asset_id',
        'workstation_id',
        'building_id',
        'department_id',
        'description',
        'status',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry_date',
        'vendor',
        'vendor_id',
        'condition',
        'depreciation',
        'depreciation_rate',
        'depreciation_period',
        'depreciation_method',
        'useful_life_years',
        'model',
        'make',
        'codeno',
        'disposal_date',
        'added_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
        'disposal_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'depreciation' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
    ];

    public function assetClass()
    {
        return $this->belongsTo(assetclass::class, 'asset_class_id');
    }

    public function facilityLocation()
    {
        return $this->belongsTo(facilitylocation::class, 'facility_location_id');
    }

    public function asset()
    {
        return $this->belongsTo(asset::class, 'asset_id');
    }

    public function workstation()
    {
        return $this->belongsTo(workstations::class, 'workstation_id');
    }

    public function building()
    {
        return $this->belongsTo(building::class, 'building_id');
    }

    public function department()
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function vendorRelation()
    {
        return $this->belongsTo(vendors::class, 'vendor_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
