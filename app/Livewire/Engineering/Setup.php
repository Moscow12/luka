<?php

namespace App\Livewire\Engineering;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Employee;
use App\Models\Engineering\OxygenPlant;
use App\Models\Engineering\ProblemCategory;
use App\Models\Engineering\Section;
use App\Models\Engineering\Setting;
use App\Services\Engineering\EngineeringAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Engineering master data: sections and their staff, problem categories (including the
 * emergency list), KPI weights and oxygen plants.
 */
class Setup extends Component
{
    use InteractsWithEngineering;

    public const TABS = ['sections' => 'Sections & staff', 'categories' => 'Problem categories', 'weights' => 'KPI weights', 'plants' => 'Oxygen plants'];

    public const WEIGHT_LABELS = [
        'jobs' => '% jobs completed', 'ppm' => '% PPM completed', 'tat' => 'Average turnaround time', 'uptime' => '% equipment uptime',
        'satisfaction' => 'Satisfaction score', 'five_s' => '% 5S score', 'oxygen' => '% oxygen purity (shared between plants)',
    ];

    #[Url]
    public string $tab = 'sections';

    // Section whose staff or categories are shown
    #[Url]
    public string $sectionId = '';

    /** @var array{code:string, name:string} */
    public array $newSection = ['code' => '', 'name' => ''];

    public string $staffSearch = '';

    /** @var array{name:string, emergency:bool} */
    public array $newCategory = ['name' => '', 'emergency' => false];

    /** @var array<string, string> */
    public array $weights = [];

    /** @var array{name:string, responsible_employee_id:string} */
    public array $newPlant = ['name' => '', 'responsible_employee_id' => ''];

    public function mount(): void
    {
        abort_unless(EngineeringAccess::canSetup(Auth::user()), 403);

        $this->sectionId = $this->sectionId ?: (string) Section::orderBy('sort_order')->value('id');
        $this->weights = array_map('strval', Setting::read('kpi.weights'));
    }

    public function setTab(string $tab): void
    {
        $this->tab = array_key_exists($tab, self::TABS) ? $tab : 'sections';
    }

    /* ---------- Sections & staff ---------- */

    public function addSection(): void
    {
        $this->validate([
            'newSection.code' => ['required', 'alpha_dash', 'max:30', Rule::unique('eng_sections', 'code')],
            'newSection.name' => ['required', 'string', 'max:80'],
        ], [], ['newSection.code' => 'code', 'newSection.name' => 'name']);

        $section = Section::create([
            'code' => strtoupper($this->newSection['code']),
            'name' => $this->newSection['name'],
            'sort_order' => Section::max('sort_order') + 1,
        ]);

        $this->newSection = ['code' => '', 'name' => ''];
        $this->sectionId = $section->id;
        session()->flash('success', "Section {$section->name} added.");
    }

    public function toggleSection(string $sectionId): void
    {
        $section = Section::findOrFail($sectionId);
        $section->update(['status' => $section->status === 'active' ? 'inactive' : 'active']);
    }

    public function addStaff(string $employeeId): void
    {
        Section::findOrFail($this->sectionId)->staff()->syncWithoutDetaching([$employeeId => ['is_head' => false]]);
        $this->staffSearch = '';
    }

    public function toggleHead(string $employeeId): void
    {
        $section = Section::findOrFail($this->sectionId);
        $isHead = (bool) $section->staff()->where('employees.id', $employeeId)->first()?->pivot->is_head;

        $section->staff()->updateExistingPivot($employeeId, ['is_head' => ! $isHead]);
    }

    /** Membership only: the person's job history is untouched. */
    public function removeStaff(string $employeeId): void
    {
        Section::findOrFail($this->sectionId)->staff()->detach($employeeId);
    }

    /* ---------- Categories ---------- */

    public function addCategory(): void
    {
        $this->validate(['newCategory.name' => ['required', 'string', 'max:200']], [], ['newCategory.name' => 'category']);

        // Emergency criteria continue the EM-nn numbering
        $code = null;
        if ($this->newCategory['emergency']) {
            $last = ProblemCategory::whereNotNull('emergency_code')->pluck('emergency_code')->map(fn (string $c) => (int) substr($c, 3))->max() ?? 0;
            $code = sprintf('EM-%02d', $last + 1);
        }

        ProblemCategory::create(['section_id' => $this->sectionId, 'name' => $this->newCategory['name'], 'emergency_code' => $code]);
        $this->newCategory = ['name' => '', 'emergency' => false];
    }

    public function toggleCategory(string $categoryId): void
    {
        $category = ProblemCategory::findOrFail($categoryId);
        $category->update(['status' => $category->status === 'active' ? 'inactive' : 'active']);
    }

    /* ---------- KPI weights ---------- */

    public function saveWeights(): void
    {
        $this->validate(
            collect(self::WEIGHT_LABELS)->mapWithKeys(fn ($label, $key) => ["weights.{$key}" => ['required', 'numeric', 'min:0', 'max:100']])->all(),
            [],
            collect(self::WEIGHT_LABELS)->mapWithKeys(fn ($label, $key) => ["weights.{$key}" => $label])->all(),
        );

        $total = round(array_sum(array_map('floatval', $this->weights)), 2);

        if ($total !== 100.0) {
            $this->addError('weights', "The weights must total 100%. They currently total {$total}%.");

            return;
        }

        Setting::write('kpi.weights', array_map('floatval', array_intersect_key($this->weights, self::WEIGHT_LABELS)), Auth::id());
        session()->flash('success', 'KPI weights saved. They apply to every report run from now on.');
    }

    /* ---------- Oxygen plants ---------- */

    public function addPlant(): void
    {
        $this->validate([
            'newPlant.name' => ['required', 'string', 'max:80'],
            'newPlant.responsible_employee_id' => ['nullable', Rule::exists('employees', 'id')],
        ], [], ['newPlant.name' => 'plant name']);

        OxygenPlant::create(['name' => $this->newPlant['name'], 'responsible_employee_id' => $this->newPlant['responsible_employee_id'] ?: null]);
        $this->newPlant = ['name' => '', 'responsible_employee_id' => ''];
    }

    public function setPlantResponsible(string $plantId, string $employeeId): void
    {
        OxygenPlant::findOrFail($plantId)->update(['responsible_employee_id' => $employeeId ?: null]);
    }

    public function togglePlant(string $plantId): void
    {
        $plant = OxygenPlant::findOrFail($plantId);
        $plant->update(['status' => $plant->status === 'active' ? 'inactive' : 'active']);
    }

    public function render()
    {
        $section = Section::with(['staff' => fn ($q) => $q->orderBy('first_name')])->find($this->sectionId);
        $search = trim($this->staffSearch);

        return view('livewire.engineering.setup', [
            'sections' => Section::orderBy('sort_order')->withCount('staff')->get(),
            'section' => $section,
            'staffMatches' => $section && mb_strlen($search) >= 2
                ? Employee::where('status', 'Active')
                    ->whereNotIn('id', $section->staff->pluck('id'))
                    ->where(fn (Builder $q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('employee_no', 'like', "%{$search}%"))
                    ->with('department:id,name')->orderBy('first_name')->limit(8)->get()
                : collect(),
            'categories' => $this->tab === 'categories' && $section ? $section->categories()->orderByRaw('emergency_code is null')->orderBy('emergency_code')->orderBy('name')->get() : collect(),
            'weightTotal' => round(array_sum(array_map('floatval', $this->weights)), 2),
            'plants' => $this->tab === 'plants' ? OxygenPlant::with('responsible')->orderBy('name')->get() : collect(),
            'engineers' => $this->tab === 'plants'
                ? Employee::whereIn('id', DB::table('eng_section_staff')->select('employee_id'))->orderBy('first_name')->get(['id', 'first_name', 'last_name'])
                : collect(),
        ]);
    }
}
