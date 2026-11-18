<?php

namespace App\Livewire\Chop;

use App\Models\chopactivities;
use App\Models\ChopObjective;
use App\Models\ChopTarget;
use App\Models\FinancialYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * CHOP Setup → Objectives & Targets. Objectives are long-lived; each financial year
 * gets its own targets under them, and activities (planned interventions) link to a target.
 */
class ObjectivesTargets extends Component
{
    public $filterFinancialYear = '';

    public $search = '';

    // Objective modal
    public $showObjectiveModal = false;

    public $objectiveId = null;

    public $objectiveCode = '';

    public $objectiveName = '';

    public $objectiveDescription = '';

    public $objectiveSortOrder = 0;

    public $objectiveStatus = 'active';

    // Target modal
    public $showTargetModal = false;

    public $targetId = null;

    public $targetObjectiveId = '';

    public $targetFinancialYearId = '';

    public $targetCode = '';

    public $targetDescription = '';

    public $targetSortOrder = 0;

    public $targetStatus = 'active';

    // Copy targets from another year
    public $showCopyModal = false;

    public $copyFromYearId = '';

    public function mount()
    {
        $this->filterFinancialYear = FinancialYear::current()?->id ?? '';
    }

    protected function authorizeManage(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->isSuperAdmin() || $user->can('manage-chop')), 403);
    }

    // ── Objectives ────────────────────────────────────────────────

    public function openObjectiveModal($id = null)
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $objective = $id ? ChopObjective::findOrFail($id) : null;
        $this->objectiveId = $objective?->id;
        $this->objectiveCode = $objective?->code ?? '';
        $this->objectiveName = $objective?->name ?? '';
        $this->objectiveDescription = $objective?->description ?? '';
        $this->objectiveSortOrder = $objective?->sort_order ?? (int) ChopObjective::max('sort_order') + 1;
        $this->objectiveStatus = $objective?->status ?? 'active';
        $this->showObjectiveModal = true;
    }

    public function saveObjective()
    {
        $this->authorizeManage();
        $this->objectiveName = Str::squish((string) $this->objectiveName);

        $this->validate([
            'objectiveCode' => ['nullable', 'string', 'max:20'],
            'objectiveName' => ['required', 'string', 'max:500', Rule::unique('chop_objectives', 'name')->ignore($this->objectiveId)],
            'objectiveDescription' => ['nullable', 'string', 'max:5000'],
            'objectiveSortOrder' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'objectiveStatus' => ['required', 'in:active,inactive'],
        ], [], [
            'objectiveCode' => 'code', 'objectiveName' => 'objective', 'objectiveDescription' => 'description',
            'objectiveSortOrder' => 'order', 'objectiveStatus' => 'status',
        ]);

        $data = [
            'code' => trim((string) $this->objectiveCode) ?: null,
            'name' => $this->objectiveName,
            'description' => trim((string) $this->objectiveDescription) ?: null,
            'sort_order' => (int) $this->objectiveSortOrder,
            'status' => $this->objectiveStatus,
        ];

        if ($this->objectiveId) {
            ChopObjective::findOrFail($this->objectiveId)->update($data);
        } else {
            ChopObjective::create($data + ['added_by' => Auth::id()]);
        }

        $this->showObjectiveModal = false;
        session()->flash('success', $this->objectiveId ? 'Objective updated.' : 'Objective added.');
    }

    public function deleteObjective($id)
    {
        $this->authorizeManage();
        $objective = ChopObjective::withCount('targets')->findOrFail($id);

        if ($objective->targets_count > 0) {
            session()->flash('error', "\"{$objective->name}\" has {$objective->targets_count} target(s) across all years. Delete or move them first, or set the objective to inactive.");

            return;
        }

        $objective->delete();
        session()->flash('success', 'Objective deleted.');
    }

    // ── Targets ───────────────────────────────────────────────────

    public function openTargetModal($id = null, $objectiveId = null)
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $target = $id ? ChopTarget::findOrFail($id) : null;
        $this->targetId = $target?->id;
        $this->targetObjectiveId = $target?->objective_id ?? ($objectiveId ?? '');
        $this->targetFinancialYearId = $target?->financial_year_id ?? $this->filterFinancialYear;
        $this->targetCode = $target?->code ?? '';
        $this->targetDescription = $target?->description ?? '';
        $this->targetSortOrder = $target?->sort_order
            ?? (int) ChopTarget::where('objective_id', $this->targetObjectiveId)->forYear($this->targetFinancialYearId)->max('sort_order') + 1;
        $this->targetStatus = $target?->status ?? 'active';
        $this->showTargetModal = true;
    }

    public function saveTarget()
    {
        $this->authorizeManage();
        $this->targetDescription = Str::squish((string) $this->targetDescription);

        $this->validate([
            'targetObjectiveId' => ['required', 'exists:chop_objectives,id'],
            'targetFinancialYearId' => ['required', 'exists:financial_years,id'],
            'targetCode' => ['nullable', 'string', 'max:20'],
            'targetDescription' => [
                'required', 'string', 'max:2000',
                // Same statement twice under one objective in one year is a mistake
                Rule::unique('chop_targets', 'description')->ignore($this->targetId)
                    ->where('objective_id', $this->targetObjectiveId)
                    ->where('financial_year_id', $this->targetFinancialYearId),
            ],
            'targetSortOrder' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'targetStatus' => ['required', 'in:active,inactive'],
        ], [
            'targetDescription.unique' => 'This target already exists under the objective for that financial year.',
        ], [
            'targetObjectiveId' => 'objective', 'targetFinancialYearId' => 'financial year', 'targetCode' => 'code',
            'targetDescription' => 'target', 'targetSortOrder' => 'order', 'targetStatus' => 'status',
        ]);

        $data = [
            'objective_id' => $this->targetObjectiveId,
            'financial_year_id' => $this->targetFinancialYearId,
            'code' => trim((string) $this->targetCode) ?: null,
            'description' => $this->targetDescription,
            'sort_order' => (int) $this->targetSortOrder,
            'status' => $this->targetStatus,
        ];

        if ($this->targetId) {
            $target = ChopTarget::withCount('activities')->findOrFail($this->targetId);

            if ($target->activities_count > 0 && $target->financial_year_id !== $this->targetFinancialYearId) {
                $this->addError('targetFinancialYearId', "{$target->activities_count} activity(ies) of the current year are linked to this target, so its year can't change.");

                return;
            }

            $target->update($data);
        } else {
            ChopTarget::create($data + ['added_by' => Auth::id()]);
        }

        $this->showTargetModal = false;
        session()->flash('success', $this->targetId ? 'Target updated.' : 'Target added.');
    }

    public function deleteTarget($id)
    {
        $this->authorizeManage();
        $target = ChopTarget::withCount('activities')->findOrFail($id);

        if ($target->activities_count > 0) {
            session()->flash('error', "This target has {$target->activities_count} linked activity(ies). Re-assign them first, or set the target to inactive.");

            return;
        }

        $target->delete();
        session()->flash('success', 'Target deleted.');
    }

    // ── Copy targets between years ───────────────────────────────

    public function openCopyModal()
    {
        $this->authorizeManage();
        $this->resetErrorBag();
        $this->copyFromYearId = '';
        $this->showCopyModal = true;
    }

    public function copyTargets()
    {
        $this->authorizeManage();

        $this->validate([
            'filterFinancialYear' => ['required', 'exists:financial_years,id'],
            'copyFromYearId' => ['required', 'exists:financial_years,id', 'different:filterFinancialYear'],
        ], [
            'filterFinancialYear.required' => 'Choose the financial year to copy into first.',
            'copyFromYearId.different' => 'Choose a different year from the one you are copying into.',
        ], ['copyFromYearId' => 'year to copy from']);

        $existing = ChopTarget::where('financial_year_id', $this->filterFinancialYear)
            ->get(['objective_id', 'description'])
            ->map(fn ($t) => $t->objective_id.'|'.mb_strtolower($t->description))
            ->flip();

        $copied = 0;
        $skipped = 0;

        DB::transaction(function () use ($existing, &$copied, &$skipped) {
            foreach (ChopTarget::where('financial_year_id', $this->copyFromYearId)->get() as $source) {
                if ($existing->has($source->objective_id.'|'.mb_strtolower($source->description))) {
                    $skipped++;

                    continue;
                }

                ChopTarget::create([
                    'objective_id' => $source->objective_id,
                    'financial_year_id' => $this->filterFinancialYear,
                    'code' => $source->code,
                    'description' => $source->description,
                    'sort_order' => $source->sort_order,
                    'status' => $source->status,
                    'added_by' => Auth::id(),
                ]);
                $copied++;
            }
        });

        $this->showCopyModal = false;
        session()->flash('success', "Copied {$copied} target(s)".($skipped ? ", skipped {$skipped} already present" : '').'. Review the wording, e.g. "by June" dates.');
    }

    public function render()
    {
        $search = trim((string) $this->search);
        $fy = $this->filterFinancialYear ?: null;

        $targetScope = fn ($q) => $q->forYear($fy)
            ->when($search !== '', fn ($q) => $q->where('description', 'like', "%{$search}%"))
            ->withCount('activities')
            ->withSum('activities', 'planned_amount')
            ->with('financialYear:id,name')
            ->ordered();

        $objectives = ChopObjective::query()
            ->with(['targets' => $targetScope])
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereHas('targets', fn ($t) => $t->forYear($fy)->where('description', 'like', "%{$search}%"))))
            ->ordered()
            ->get();

        $unlinked = $fy ? chopactivities::where('financial_year_id', $fy)->whereNull('target_id')->count() : 0;

        return view('livewire.chop.objectives-targets', [
            'objectives' => $objectives,
            'financialYears' => FinancialYear::orderBy('start_date', 'desc')->get(['id', 'name']),
            'allObjectives' => ChopObjective::ordered()->get(['id', 'code', 'name', 'status']),
            'unlinkedActivities' => $unlinked,
            'canManage' => Auth::user()->isSuperAdmin() || Auth::user()->can('manage-chop'),
        ]);
    }
}
