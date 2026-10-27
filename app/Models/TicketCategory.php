<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'default_department_id',
        'default_sla_hours',
        'status',
        'added_by',
    ];

    public function defaultDepartment(): BelongsTo
    {
        return $this->belongsTo(departments::class, 'default_department_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
