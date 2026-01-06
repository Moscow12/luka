<?php

namespace App\Livewire\Performance\TitleKpis;

use App\Models\Jobtitle;
use App\Models\TitleKpi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManageTitleKpis extends Component
{
    use WithPagination;

    // Search and Filters
    public $search = '';

    public $jobTitleFilter = '';

    public $typeFilter = '';

    public $mandatoryFilter = '';

    public $perPage = 10;

    // Modal States
    public $showModal = false;

    public $modalMode = 'create';

    // Selected Records
    public $selectedKpi = null;

    // KPI Form Fields
    public $kpi_id;

    public $job_title_id;

    public $kpi_name;

    public $description;

    public $kpi_type = 'quantitative';

    public $measurement_type = 'numeric';

    public $weight;

    public $target_value;

    public $target_unit;

    public $is_mandatory = false;

    public $scoring_criteria;

    public $display_order;

    // Pagination reset on search/filter changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingJobTitleFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingMandatoryFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'jobTitleFilter', 'typeFilter', 'mandatoryFilter']);
        $this->resetPage();
    }

    public function mount()
    {
        //
    }

    // KPI CRUD Operations
    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->showModal = true;
        $this->resetKpiForm();
        $this->kpi_type = 'quantitative';
        $this->measurement_type = 'numeric';
        $this->is_mandatory = false;
    }

    public function openEditModal($kpiId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->showModal = true;

        $kpi = TitleKpi::findOrFail($kpiId);
        $this->kpi_id = $kpi->id;
        $this->job_title_id = $kpi->job_title_id;
        $this->kpi_name = $kpi->kpi_name;
        $this->description = $kpi->description;
        $this->kpi_type = $kpi->kpi_type;
        $this->measurement_type = $kpi->measurement_type;
        $this->weight = $kpi->weight;
        $this->target_value = $kpi->target_value;
        $this->target_unit = $kpi->target_unit;
        $this->is_mandatory = $kpi->is_mandatory;
        $this->scoring_criteria = $kpi->scoring_criteria;
        $this->display_order = $kpi->display_order;
    }

    public function save()
    {
        $this->validate($this->getValidationRules());

        try {
            DB::beginTransaction();

            if ($this->modalMode === 'edit' && $this->kpi_id) {
                $kpi = TitleKpi::findOrFail($this->kpi_id);
                $kpi->update([
                    'job_title_id' => $this->job_title_id,
                    'kpi_name' => $this->kpi_name,
                    'description' => $this->description,
                    'kpi_type' => $this->kpi_type,
                    'measurement_type' => $this->measurement_type,
                    'weight' => $this->weight,
                    'target_value' => $this->target_value,
                    'target_unit' => $this->target_unit,
                    'is_mandatory' => $this->is_mandatory,
                    'scoring_criteria' => $this->scoring_criteria,
                    'display_order' => $this->display_order,
                ]);
                session()->flash('success', 'Title KPI updated successfully!');
            } else {
                TitleKpi::create([
                    'job_title_id' => $this->job_title_id,
                    'kpi_name' => $this->kpi_name,
                    'description' => $this->description,
                    'kpi_type' => $this->kpi_type,
                    'measurement_type' => $this->measurement_type,
                    'weight' => $this->weight,
                    'target_value' => $this->target_value,
                    'target_unit' => $this->target_unit,
                    'is_mandatory' => $this->is_mandatory,
                    'scoring_criteria' => $this->scoring_criteria,
                    'display_order' => $this->display_order ?? 0,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                ]);
                session()->flash('success', 'Title KPI created successfully!');
            }

            DB::commit();
            $this->showModal = false;
            $this->resetKpiForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function delete($kpiId)
    {
        try {
            $kpi = TitleKpi::findOrFail($kpiId);

            DB::beginTransaction();
            $kpi->delete();
            DB::commit();

            session()->flash('success', 'Title KPI deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function toggleActive($kpiId)
    {
        try {
            $kpi = TitleKpi::findOrFail($kpiId);

            DB::beginTransaction();
            $kpi->update([
                'is_active' => ! $kpi->is_active,
            ]);
            DB::commit();

            $status = $kpi->is_active ? 'activated' : 'deactivated';
            session()->flash('success', "Title KPI {$status} successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    // Validation Rules
    protected function getValidationRules()
    {
        return [
            'job_title_id' => ['required', 'exists:jobtitles,id'],
            'kpi_name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => ['nullable', 'string'],
            'kpi_type' => ['required', 'in:quantitative,qualitative'],
            'measurement_type' => ['required', 'in:numeric,percentage,rating,binary,text'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_value' => ['nullable', 'numeric'],
            'target_unit' => ['nullable', 'string', 'max:100'],
            'is_mandatory' => ['boolean'],
            'scoring_criteria' => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    // Helper Methods
    protected function resetKpiForm()
    {
        $this->reset([
            'kpi_id',
            'job_title_id',
            'kpi_name',
            'description',
            'kpi_type',
            'measurement_type',
            'weight',
            'target_value',
            'target_unit',
            'is_mandatory',
            'scoring_criteria',
            'display_order',
        ]);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetKpiForm();
    }

    public function render()
    {
        // Build query with eager loading
        $kpisQuery = TitleKpi::query()
            ->with(['jobtitle', 'creator']);

        // Apply search filter
        if ($this->search) {
            $kpisQuery->where(function ($query) {
                $query->where('kpi_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('jobtitle', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Apply job title filter
        if ($this->jobTitleFilter) {
            $kpisQuery->where('job_title_id', $this->jobTitleFilter);
        }

        // Apply type filter
        if ($this->typeFilter) {
            $kpisQuery->where('kpi_type', $this->typeFilter);
        }

        // Apply mandatory filter
        if ($this->mandatoryFilter !== '') {
            $kpisQuery->where('is_mandatory', $this->mandatoryFilter);
        }

        // Order by job title and display order
        $kpisQuery->orderBy('display_order')
            ->orderBy('created_at', 'desc');

        // Paginate
        $kpis = $kpisQuery->paginate($this->perPage);

        // Calculate statistics
        $totalKpis = TitleKpi::count();
        $activeKpis = TitleKpi::where('is_active', true)->count();
        $mandatoryKpis = TitleKpi::where('is_mandatory', true)->count();
        $jobTitlesCovered = TitleKpi::distinct('job_title_id')->count('job_title_id');

        // Get job titles for filters
        $jobTitles = Jobtitle::orderBy('name')->get();

        return view('livewire.performance.title-kpis.manage-title-kpis', [
            'kpis' => $kpis,
            'totalKpis' => $totalKpis,
            'activeKpis' => $activeKpis,
            'mandatoryKpis' => $mandatoryKpis,
            'jobTitlesCovered' => $jobTitlesCovered,
            'jobTitles' => $jobTitles,
        ]);
    }
}
