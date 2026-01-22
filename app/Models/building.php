<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class building extends Model
{
    use HasUuids;
    protected $table = 'buildings';
    protected $fillable = ['name', 'workstation_id', 'added_by'];

    public function workstation()
    {
        return $this->belongsTo(workstations::class, 'workstation_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
