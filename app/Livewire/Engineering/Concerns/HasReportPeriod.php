<?php

namespace App\Livewire\Engineering\Concerns;

use App\Support\Engineering\Eng;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/**
 * Daily / weekly / monthly / annual / custom period selector shared by the reports.
 */
trait HasReportPeriod
{
    public const PERIODS = [
        'day' => 'Daily',
        'week' => 'Weekly',
        'month' => 'Monthly',
        'year' => 'Annual',
        'custom' => 'Selected dates',
    ];

    #[Url]
    public string $period = 'month';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function mountHasReportPeriod(): void
    {
        if ($this->dateFrom === '' || $this->dateTo === '') {
            $this->applyPeriod();
        }
    }

    public function updatedPeriod(): void
    {
        $this->applyPeriod();
    }

    /** Sets the dates to the current day, week, month or year. Custom keeps what was typed. */
    protected function applyPeriod(): void
    {
        $today = Eng::now();

        [$from, $to] = match ($this->period) {
            'day' => [$today->copy(), $today->copy()],
            'week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            'custom' => [Carbon::parse($this->dateFrom ?: $today), Carbon::parse($this->dateTo ?: $today)],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };

        $this->dateFrom = $from->toDateString();
        $this->dateTo = $to->toDateString();
    }

    /**
     * The selected dates as UTC instants covering whole hospital-local days.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function periodRange(): array
    {
        $timezone = config('engineering.timezone');

        return [
            Carbon::parse($this->dateFrom, $timezone)->startOfDay()->utc(),
            Carbon::parse($this->dateTo, $timezone)->endOfDay()->utc(),
        ];
    }

    protected function periodLabel(): string
    {
        return Carbon::parse($this->dateFrom)->format('d M Y').' - '.Carbon::parse($this->dateTo)->format('d M Y');
    }
}
