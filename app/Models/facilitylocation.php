<?php

namespace App\Models;

use App\Models\Concerns\HasActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A room / place inside a building. This is the single room register shared by the asset
 * register and engineering; each room belongs to a department.
 */
class facilitylocation extends Model
{
    use HasActiveStatus, HasUuids, SoftDeletes;

    protected $table = 'facilitylocations';

    protected $fillable = ['name', 'code', 'floor', 'building_id', 'workstation_id', 'department_id', 'status', 'added_by'];

    public function building()
    {
        return $this->belongsTo(building::class, 'building_id');
    }

    public function workstation()
    {
        return $this->belongsTo(workstations::class, 'workstation_id');
    }

    public function department()
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function assetRegistries()
    {
        return $this->hasMany(assetregistry::class, 'facility_location_id');
    }

    /**
     * Rooms of a department, plus rooms not yet given a department (older records still to be
     * cleaned up) so existing data stays usable.
     */
    public function scopeForDepartment(Builder $query, ?string $departmentId, bool $includeUnassigned = true): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where($q->qualifyColumn('department_id'), $departmentId)
            ->when($includeUnassigned, fn (Builder $w) => $w->orWhereNull($w->qualifyColumn('department_id'))));
    }

    /** "Main Block / Floor 2 / ICU 204" */
    public function label(): string
    {
        return collect([$this->building?->name, $this->floor, $this->name])->filter()->implode(' / ');
    }
}
