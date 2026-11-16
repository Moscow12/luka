<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class building extends Model
{
    use HasActiveStatus, HasUuids, SoftDeletes;

    protected $table = 'buildings';

    protected $fillable = ['name', 'workstation_id', 'status', 'added_by'];

    public function workstation()
    {
        return $this->belongsTo(workstations::class, 'workstation_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
