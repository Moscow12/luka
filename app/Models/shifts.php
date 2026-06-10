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

    /**
     * Whether the shift spans midnight (ends on the day after it starts),
     * e.g. a night shift running 07:31 PM -> 07:31 AM.
     */
    public function crossesMidnight(): bool
    {
        if (! $this->start_time || ! $this->end_time) {
            return false;
        }

        return \Carbon\Carbon::parse($this->end_time)
            ->lessThanOrEqualTo(\Carbon\Carbon::parse($this->start_time));
    }

    /**
     * Total length of the shift in minutes, accounting for shifts that
     * cross midnight.
     */
    public function durationInMinutes(): int
    {
        if (! $this->start_time || ! $this->end_time) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return (int) $start->diffInMinutes($end);
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
