<?php

namespace App\Support\Engineering;

use App\Models\Employee;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Display helpers shared by engineering screens and exports.
 */
class Eng
{
    /** Stored UTC timestamp shown in hospital local time. */
    public static function dt(?CarbonInterface $value, string $format = 'd M Y H:i'): string
    {
        return $value ? $value->copy()->timezone(config('engineering.timezone'))->format($format) : '—';
    }

    public static function date(?CarbonInterface $value, string $format = 'd M Y'): string
    {
        return $value ? $value->format($format) : '—';
    }

    /** "Now" in hospital local time, for deciding which day or week something falls in. */
    public static function now(): Carbon
    {
        return now(config('engineering.timezone'));
    }

    public static function name(?Employee $employee): string
    {
        return $employee ? trim($employee->first_name.' '.$employee->last_name) : '—';
    }

    public static function money(float|string|null $amount): string
    {
        return $amount === null ? '—' : number_format((float) $amount, 0).' '.config('engineering.currency');
    }

    /** Elapsed time as "45m", "5h" or "3d". */
    public static function elapsed(?CarbonInterface $since): string
    {
        if (! $since) {
            return '—';
        }

        $minutes = (int) $since->diffInMinutes(now());

        return match (true) {
            $minutes < 60 => $minutes.'m',
            $minutes < 1440 => intdiv($minutes, 60).'h',
            default => intdiv($minutes, 1440).'d',
        };
    }
}
