<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shifts extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'shifts';

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'status',
        'is_default',
        'count_early',
        'count_late',
        'added_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * The shift used as the fallback when an employee has no roster.
     */
    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function rosters()
    {
        return $this->hasMany(employeeroster::class, 'shift_id');
    }
}
