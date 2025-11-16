<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinancialYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When a financial year is set as current, unset all others
        static::saving(function ($financialYear) {
            if ($financialYear->is_current) {
                static::where('id', '!=', $financialYear->id)
                    ->update(['is_current' => false]);
            }
        });
    }

    /**
     * Get the current financial year
     */
    public static function current()
    {
        return static::where('is_current', true)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Check if a date falls within this financial year
     */
    public function containsDate(Carbon $date): bool
    {
        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Set this financial year as current
     */
    public function setAsCurrent(): void
    {
        DB::transaction(function () {
            // Unset all other financial years
            static::where('id', '!=', $this->id)
                ->update(['is_current' => false]);

            // Set this as current
            $this->update(['is_current' => true]);
        });
    }

    /**
     * Update the current financial year based on today's date
     */
    public static function updateCurrentFinancialYear(): void
    {
        $today = Carbon::today();

        // Find the financial year that contains today's date
        $currentFY = static::where('status', 'active')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentFY) {
            $currentFY->setAsCurrent();
        }
    }

    /**
     * Check if this financial year is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope to get only active financial years
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get formatted date range
     */
    public function getDateRangeAttribute(): string
    {
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }
}
