<?php

namespace App\Livewire\Engineering\Jobs;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\RootCauseAnalysis;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\NumberSequence;
use App\Support\Engineering\Eng;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * 5-Why root cause analysis for a job: what was observed, who took part, the chain of
 * whys with evidence, and the actions that follow.
 */
class RootCause extends Component
{
    use InteractsWithEngineering;

    protected const MIN_PARTICIPANTS = 2;

    protected const FULL_CHAIN = 5;

    public string $jobId;

    /** @var array<string, ?string> text fields keyed by column */
    public array $form = [
        'session_date' => null,
        'indication_of_problem' => '',
        'client_observations' => '',
        'actions_taken' => '',
        'actual_problem' => '',
        'probable_cause_selected' => '',
        'root_cause' => '',
        'early_stop_justification' => '',
        'corrective_action' => '',
        'preventive_action' => '',
        'responsible_employee_id' => null,
        'action_target_date' => null,
    ];

    /** @var array<int, string> */
    public array $probableCauses = [''];

    /** @var array<int, array{name:string, role:string, organisation:string}> */
    public array $participants = [];

    /** @var array<int, array{answer:string, evidence:string}> */
    public array $whys = [];

    public function mount(MaintenanceJob $job): void
    {
        abort_unless(EngineeringAccess::canViewJob(Auth::user(), $job), 403);

        $this->jobId = $job->id;
        $rca = $job->rca;

        if ($rca) {
            foreach (array_keys($this->form) as $field) {
                $value = $rca->{$field};
                $this->form[$field] = $value instanceof DateTimeInterface ? $value->format('Y-m-d') : $value;
            }
            $this->probableCauses = $rca->probable_causes ?: [''];
            $this->participants = $rca->participants ?? [];
            $this->whys = $rca->whys ?? [];
        } else {
            // Start with the job team as participants and one empty "why"
            $this->form['session_date'] = Eng::now()->toDateString();
            $this->participants = collect([$job->engineer])->merge($job->assistants)->filter()
                ->map(fn ($employee) => ['name' => Eng::name($employee), 'role' => $job->section->name, 'organisation' => config('app.name')])
                ->values()->all();
            $this->whys = [['answer' => '', 'evidence' => '']];
        }
    }

    protected function job(): MaintenanceJob
    {
        return MaintenanceJob::with('rca')->findOrFail($this->jobId);
    }

    /** The job team owns the analysis while it is a draft. */
    protected function canEdit(MaintenanceJob $job): bool
    {
        return EngineeringAccess::isOnJob(Auth::user(), $job) && ($job->rca?->isEditable() ?? true);
    }

    public function addCause(): void
    {
        $this->probableCauses[] = '';
    }

    public function removeCause(int $index): void
    {
        unset($this->probableCauses[$index]);
        $this->probableCauses = array_values($this->probableCauses) ?: [''];
    }

    public function addParticipant(): void
    {
        $this->participants[] = ['name' => '', 'role' => '', 'organisation' => ''];
    }

    public function removeParticipant(int $index): void
    {
        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);
    }

    public function addWhy(): void
    {
        $this->whys[] = ['answer' => '', 'evidence' => ''];
    }

    public function removeWhy(int $index): void
    {
        unset($this->whys[$index]);
        $this->whys = array_values($this->whys);
    }

    public function saveDraft(NumberSequence $numbers): void
    {
        $this->persist($numbers, 'draft');
        session()->flash('success', 'Draft saved.');
    }

    /** A completed analysis needs every section filled and evidence behind every answer. */
    public function complete(NumberSequence $numbers): void
    {
        $this->validate([
            'form.indication_of_problem' => ['required', 'string'],
            'form.client_observations' => ['required', 'string'],
            'form.actions_taken' => ['required', 'string'],
            'form.actual_problem' => ['required', 'string'],
            'form.probable_cause_selected' => ['required', 'string'],
            'form.root_cause' => ['required', 'string'],
            'form.early_stop_justification' => [count($this->filledWhys()) < self::FULL_CHAIN ? 'required' : 'nullable', 'nullable', 'string'],
            'participants' => ['array', 'min:'.self::MIN_PARTICIPANTS],
            'participants.*.name' => ['required', 'string', 'max:150'],
            'whys' => ['array', 'min:1'],
            'whys.*.answer' => ['required', 'string'],
            'whys.*.evidence' => ['required', 'string'],
        ], [
            'participants.min' => 'Record at least '.self::MIN_PARTICIPANTS.' participants: a root cause analysis is not a solo exercise.',
            'whys.*.evidence.required' => 'Every answer needs its evidence.',
            'whys.*.answer.required' => 'Answer this why, or remove it.',
            'form.early_stop_justification.required' => 'Explain why the chain stops before the fifth why.',
            'form.probable_cause_selected.required' => 'Select the probable cause carried into the 5-Why.',
        ], [
            'form.indication_of_problem' => 'indication of the problem', 'form.client_observations' => 'information from the client',
            'form.actions_taken' => 'actions taken', 'form.actual_problem' => 'actual problem', 'form.root_cause' => 'root cause',
            'participants.*.name' => 'participant name',
        ]);

        $this->persist($numbers, 'completed');
        session()->flash('success', 'Root cause analysis completed.');
    }

    /** HOS / HOD review closes the analysis for good. */
    public function review(): void
    {
        $job = $this->job();
        abort_unless(EngineeringAccess::canAssign(Auth::user(), $job->section_id) && $job->rca?->status === 'completed', 403);

        $job->rca->update(['status' => 'closed', 'reviewed_by' => Auth::id()]);
        session()->flash('success', 'Analysis reviewed and closed.');
    }

    public function markActionDone(): void
    {
        $job = $this->job();
        abort_unless(EngineeringAccess::canViewJob(Auth::user(), $job) && $job->rca, 403);

        $job->rca->update(['action_done_at' => now()]);
    }

    protected function persist(NumberSequence $numbers, string $status): void
    {
        $job = $this->job();
        abort_unless($this->canEdit($job), 403);

        DB::transaction(function () use ($job, $numbers, $status) {
            $attributes = array_merge(array_map(fn ($value) => $value === '' ? null : $value, $this->form), [
                'probable_causes' => array_values(array_filter(array_map('trim', $this->probableCauses))),
                'participants' => $this->participants,
                'whys' => $this->whys,
                'status' => $status,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            $job->rca
                ? $job->rca->update($attributes)
                : RootCauseAnalysis::create($attributes + ['job_id' => $job->id, 'number' => $numbers->next('RCA'), 'facilitator_id' => Auth::id()]);
        });
    }

    /**
     * @return array<int, array{answer:string, evidence:string}>
     */
    protected function filledWhys(): array
    {
        return array_filter($this->whys, fn (array $why) => filled($why['answer']));
    }

    /** Each question is phrased from the previous answer, so the chain is visibly connected. */
    public function question(int $index): string
    {
        $subject = $index === 0 ? $this->form['actual_problem'] : ($this->whys[$index - 1]['answer'] ?? '');

        return 'Why did '.(filled($subject) ? '“'.rtrim(trim($subject), '.').'”' : '…').' happen?';
    }

    public function render()
    {
        $job = MaintenanceJob::with(['requisition.department', 'requisition.room', 'equipment', 'section', 'rca.facilitator', 'rca.responsible'])->findOrFail($this->jobId);

        return view('livewire.engineering.jobs.root-cause', [
            'job' => $job,
            'rca' => $job->rca,
            'canEdit' => $this->canEdit($job),
            'canReview' => $job->rca?->status === 'completed' && EngineeringAccess::canAssign(Auth::user(), $job->section_id),
            'staff' => $job->section->staff()->orderBy('first_name')->get(['employees.id', 'first_name', 'last_name']),
            'fullChain' => self::FULL_CHAIN,
        ]);
    }
}
