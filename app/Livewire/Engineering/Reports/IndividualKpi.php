<?php

namespace App\Livewire\Engineering\Reports;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\FiveSScore;
use App\Models\Engineering\OxygenPlant;
use App\Models\Engineering\OxygenReading;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\EngineeringNotifier;
use App\Services\Engineering\KpiService;
use App\Support\Engineering\Eng;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Individual KPI for engineers: weekly weighted scores, with the uptime, 5S and oxygen
 * purity figures that feed them. An engineer sees only their own row.
 */
class IndividualKpi extends Component
{
    use InteractsWithEngineering;

    public const TABS = ['scores' => 'Weekly scores', 'uptime' => 'Equipment uptime', 'five_s' => '5S scores', 'oxygen' => 'Oxygen purity'];

    public const KPIS = ['jobs' => '% jobs', 'ppm' => '% PPM', 'tat' => 'TAT', 'uptime' => '% uptime', 'satisfaction' => 'Satisfaction', 'five_s' => '5S', 'oxygen' => 'O₂ purity'];

    // Two readings a day, Monday to Friday
    protected const EXPECTED_READINGS = 10;

    #[Url]
    public string $tab = 'scores';

    #[Url]
    public string $date = '';

    #[Url]
    public string $filterSection = '';

    // Person whose month is expanded
    public ?string $monthlyEmployeeId = null;

    /** @var array<string, string> 5S score typed per employee id */
    public array $fiveS = [];

    public string $readingPlantId = '';

    public string $readingAt = '';

    public string $readingPurity = '';

    public function mount(): void
    {
        abort_unless(EngineeringAccess::isEngineeringStaff(Auth::user()), 403);

        $this->date = $this->date ?: Eng::now()->toDateString();
        $this->readingAt = Eng::now()->format('Y-m-d\TH:i');
    }

    public function setTab(string $tab): void
    {
        $this->tab = array_key_exists($tab, self::TABS) ? $tab : 'scores';
    }

    public function shiftWeek(int $weeks): void
    {
        $this->date = Carbon::parse($this->date)->addWeeks($weeks)->toDateString();
    }

    public function showMonth(?string $employeeId): void
    {
        $this->monthlyEmployeeId = $this->monthlyEmployeeId === $employeeId ? null : $employeeId;
    }

    /** Whether the user sees more than their own row. */
    protected function seesOthers(): bool
    {
        return EngineeringAccess::canViewReports(Auth::user());
    }

    protected function rows(KpiService $kpi): Collection
    {
        $user = Auth::user();

        if (! $this->seesOthers()) {
            return $kpi->individual($this->date, null, EngineeringAccess::employee($user)?->id ?? 'none');
        }

        $allowed = EngineeringAccess::supervisedSectionIds($user);
        $sections = $this->filterSection && ($allowed === null || in_array($this->filterSection, $allowed, true))
            ? [$this->filterSection]
            : $allowed;

        return $kpi->individual($this->date, $sections);
    }

    /* ---------- 5S ---------- */

    protected function canRecordFiveS(): bool
    {
        $user = Auth::user();

        return EngineeringAccess::isManager($user) || EngineeringAccess::isHeadOfSection($user) || EngineeringAccess::canSetup($user);
    }

    public function saveFiveS(string $employeeId, KpiService $kpi): void
    {
        abort_unless($this->canRecordFiveS(), 403);

        $this->validate(["fiveS.{$employeeId}" => ['required', 'numeric', 'between:0,100']], [], ["fiveS.{$employeeId}" => '5S score']);

        // Only people in the rows this user may see
        abort_unless($this->rows($kpi)->contains('employee_id', $employeeId), 403);

        FiveSScore::updateOrCreate(
            ['employee_id' => $employeeId, 'week_start' => $kpi->weekStart($this->date)->toDateString()],
            ['score_pct' => $this->fiveS[$employeeId], 'recorded_by' => Auth::id()],
        );

        unset($this->fiveS[$employeeId]);
        session()->flash('success', '5S score recorded.');
    }

    /* ---------- Oxygen purity ---------- */

    protected function canRecordReading(OxygenPlant $plant): bool
    {
        $user = Auth::user();

        return EngineeringAccess::isManager($user)
            || EngineeringAccess::isHeadOfSection($user)
            || EngineeringAccess::employee($user)?->id === $plant->responsible_employee_id;
    }

    public function saveReading(EngineeringNotifier $notifier): void
    {
        $this->validate([
            'readingPlantId' => ['required', Rule::exists('eng_oxygen_plants', 'id')],
            'readingAt' => ['required', 'date', 'before_or_equal:tomorrow'],
            'readingPurity' => ['required', 'numeric', 'between:0,100'],
        ], [], ['readingPlantId' => 'plant', 'readingAt' => 'reading time', 'readingPurity' => 'purity']);

        $plant = OxygenPlant::with('responsible')->findOrFail($this->readingPlantId);
        abort_unless($this->canRecordReading($plant), 403);

        $at = Carbon::parse($this->readingAt, config('engineering.timezone'))->utc();

        if (OxygenReading::where('plant_id', $plant->id)->where('reading_at', $at)->exists()) {
            $this->addError('readingAt', 'A reading for this plant at that time already exists.');

            return;
        }

        $reading = OxygenReading::create(['plant_id' => $plant->id, 'reading_at' => $at, 'purity_pct' => $this->readingPurity, 'recorded_by' => Auth::id()]);

        if ($reading->isBelowMinimum()) {
            $message = "{$plant->name}: oxygen purity {$reading->purity_pct}% is below the minimum of ".config('engineering.oxygen.min_purity').'%.';
            $notifier->employee($plant->responsible, $message);
            $notifier->holders('engineering-manage', $message);
            session()->flash('error', $message.' The responsible staff have been alerted.');
        } else {
            session()->flash('success', 'Reading recorded.');
        }

        $this->readingPurity = '';
    }

    public function export(KpiService $kpi)
    {
        $rows = $this->rows($kpi);
        $colours = ['green' => 'C6E0B4', 'yellow' => 'FFE699', 'red' => 'F4B6B6'];

        $headings = ['S/N', 'Staff', 'Section', 'Jobs assigned', 'Jobs completed', '% jobs completed', 'PPM assigned', 'PPM completed', '% PPM completed', 'Avg TAT (days)'];
        foreach (self::KPIS as $label) {
            array_push($headings, "{$label} score %", "{$label} weighted");
        }
        $headings[] = 'Overall weekly score';

        return $this->exportTable(
            'Individual KPI for engineers',
            $headings,
            $rows->values()->map(function (array $row, int $i) {
                $cells = [
                    $i + 1, $row['name'], $row['section'],
                    $row['metrics']['assigned'], $row['metrics']['finished'], $row['metrics']['jobs_pct'] ?? '—',
                    $row['metrics']['ppm_assigned'], $row['metrics']['ppm_finished'], $row['metrics']['ppm_pct'] ?? '—',
                    $row['metrics']['tat_days'] ?? '—',
                ];
                foreach (array_keys(self::KPIS) as $kpiKey) {
                    array_push($cells, $row['scores'][$kpiKey] ?? '—', $row['weighted'][$kpiKey] ?? '—');
                }
                $cells[] = number_format($row['overall'], 3);

                return $cells;
            }),
            ['Week' => 'Monday '.$kpi->weekStart($this->date)->format('d M Y').' to Friday'],
            $rows->values()->map(fn (array $row) => [count($headings) - 1 => $colours[$row['band']]])->all(),
        );
    }

    public function render(KpiService $kpi)
    {
        $user = Auth::user();
        [$from, $to] = $kpi->week($this->date);
        $monday = $kpi->weekStart($this->date);

        $plants = $this->tab === 'oxygen'
            ? OxygenPlant::where('status', 'active')->with(['responsible', 'readings' => fn ($q) => $q->whereBetween('reading_at', [$from, $to])->orderBy('reading_at')->with('recordedBy:id,first_name,surname')])->orderBy('name')->get()
            : collect();

        $rows = $this->rows($kpi);

        return view('livewire.engineering.reports.individual-kpi', [
            'rows' => $rows,
            'weights' => $kpi->weights(),
            'targets' => config('engineering.kpi.targets'),
            'weekLabel' => 'Mon '.$monday->format('d M').' - Fri '.$monday->copy()->addDays(4)->format('d M Y'),
            'seesOthers' => $this->seesOthers(),
            'sections' => EngineeringAccess::selectableSections($user),
            // Never a month of someone outside the rows this user may see
            'monthly' => $rows->contains('employee_id', $this->monthlyEmployeeId) ? $kpi->monthly($this->monthlyEmployeeId, $this->date) : null,
            'uptime' => $this->tab === 'uptime' && $this->seesOthers() ? $kpi->uptimeDetail(EngineeringAccess::supervisedSectionIds($user)) : collect(),
            'canRecordFiveS' => $this->canRecordFiveS(),
            'plants' => $plants,
            'recordablePlants' => $plants->filter(fn (OxygenPlant $plant) => $this->canRecordReading($plant)),
            'expectedReadings' => self::EXPECTED_READINGS,
            'minPurity' => config('engineering.oxygen.min_purity'),
        ]);
    }
}
