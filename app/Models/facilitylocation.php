<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class facilitylocation extends Model
{
    use HasUuids;

    protected $table = 'facilitylocations';
    protected $fillable = ['name', 'building_id', 'workstation_id', 'added_by'];

    public function building()
    {
        return $this->belongsTo(building::class, 'building_id');
    }

    public function workstation()
    {
        return $this->belongsTo(workstations::class, 'workstation_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
