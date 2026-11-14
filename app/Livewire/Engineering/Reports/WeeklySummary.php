<?php

namespace App\Livewire\Engineering\Reports;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\KpiService;
use App\Support\Engineering\Eng;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * MRV weekly report (Monday to Friday): department and section figures, coloured against
 * their targets, with the improvement on the previous week.
 */
class WeeklySummary extends Component
{
    use InteractsWithEngineering;

    // key => [label, metric used for the performance colour]
    public const ROWS = [
        'assigned' => ['Total jobs assigned', null],
        'ppm_assigned' => ['Total PPM jobs assigned', null],
        'non_ppm_assigned' => ['Total non-PPM jobs assigned', null],
        'finished' => ['Total jobs finished', null],
        'ppm_finished' => ['Total PPM jobs finished', null],
        'non_ppm_finished' => ['Total non-PPM jobs finished', null],
        'jobs_pct' => ['% total jobs completed (target ≥ 90%)', 'jobs_pct'],
        'ppm_pct' => ['% completed PPM jobs (target ≥ 75%)', 'ppm_pct'],
        'open' => ['Total open jobs', null],
        'open_ppm' => ['Total open PPM jobs', null],
        'open_non_ppm' => ['Total open non-PPM jobs', null],
        'tat_days' => ['Weekly average TAT, days per job (target ≤ 2)', 'tat_days'],
        'needing_spares' => ['Jobs needing spares', null],
        'with_scores' => ['Jobs with satisfaction scores', null],
        'satisfaction_pct' => ['Weekly % average satisfaction score', 'satisfaction_pct'],
    ];

    public const VARIANCE_ROWS = [
        'jobs_pct' => '% total jobs completed variance',
        'ppm_pct' => '% completed PPM jobs variance',
        'tat_days' => 'TAT (days per job) variance',
        'satisfaction_pct' => '% average satisfaction score variance',
    ];

    protected const COLOURS = ['green' => 'C6E0B4', 'yellow' => 'FFE699', 'red' => 'F4B6B6'];

    // Symbols keep the report readable in monochrome print and for colour-blind readers
    public const SYMBOLS = ['green' => '▲', 'yellow' => '▬', 'red' => '▼'];

    #[Url]
    public string $date = '';

    public function mount(): void
    {
        abort_unless(EngineeringAccess::canViewReports(Auth::user()), 403);

        $this->date = $this->date ?: Eng::now()->toDateString();
    }

    public function shiftWeek(int $weeks): void
    {
        $this->date = Carbon::parse($this->date)->addWeeks($weeks)->toDateString();
    }

    /**
     * A head of section sees the columns of their own sections only.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function columns(KpiService $kpi): array
    {
        $columns = $kpi->weeklySummary($this->date);
        $allowed = EngineeringAccess::supervisedSectionIds(Auth::user());

        if ($allowed === null) {
            return $columns;
        }

        $codes = Section::whereIn('id', $allowed)->pluck('code')->all();

        return array_intersect_key($columns, array_flip($codes));
    }

    protected function weekLabel(KpiService $kpi): string
    {
        $monday = $kpi->weekStart($this->date);

        return 'Week '.$monday->format('W').' · Mon '.$monday->format('d M').' - Fri '.$monday->copy()->addDays(4)->format('d M Y');
    }

    public function export(KpiService $kpi)
    {
        $columns = $this->columns($kpi);
        $rows = [];
        $fills = [];

        foreach (self::ROWS as $key => [$label, $metric]) {
            $row = [$label];
            foreach (array_values($columns) as $index => $column) {
                $value = $column['current'][$key];
                $row[] = $value ?? '—';
                if ($metric && ($band = $kpi->band($metric, $value))) {
                    $fills[count($rows)][$index + 1] = self::COLOURS[$band];
                }
            }
            $rows[] = $row;
        }

        $rows[] = [];
        $rows[] = ['IMPROVEMENTS (current week - previous week)'];

        foreach (self::VARIANCE_ROWS as $key => $label) {
            $row = [$label];
            foreach (array_values($columns) as $index => $column) {
                [$delta, $band] = $kpi->variance($key, $column['current'][$key], $column['previous'][$key] ?? null);
                $row[] = $delta === null ? '—' : sprintf('%+.2f %s', $delta, self::SYMBOLS[$band]);
                if ($band) {
                    $fills[count($rows)][$index + 1] = self::COLOURS[$band];
                }
            }
            $rows[] = $row;
        }

        $rows[] = [];
        $rows[] = ['KEY - performance: green = target achieved; yellow = near target, HOS to help; red = target missed, HOS and HOD intervention required'];
        $rows[] = ['KEY - improvement: ▲ green = improved; ▬ yellow = did not improve; ▼ red = dropped in performance'];

        return $this->exportTable('MRV weekly report', ['Item', ...array_column($columns, 'label')], $rows, ['Week' => $this->weekLabel($kpi)], $fills);
    }

    public function render(KpiService $kpi)
    {
        return view('livewire.engineering.reports.weekly-summary', [
            'columns' => $this->columns($kpi),
            'weekLabel' => $this->weekLabel($kpi),
            'kpi' => $kpi,
        ]);
    }
}
