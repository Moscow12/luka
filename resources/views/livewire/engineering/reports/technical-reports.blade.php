@php use App\Support\Engineering\Eng; @endphp

<x-engineering.page title="Engineering technical report" :subtitle="$openJob ? $openJob->number.' · '.$openJob->equipmentName() : 'Per-equipment reports to management'">
    <x-slot:actions>
        @if($openJob)
            <button type="button" class="btn btn-outline-secondary" wire:click="close"><i class="fa-solid fa-arrow-left me-1"></i>All reports</button>
            @if($openJob->technicalReport)
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print / PDF</button>
                <button type="button" class="btn btn-outline-primary" wire:click="downloadWord"><i class="fa-solid fa-file-word me-1"></i>Download for Word</button>
            @endif
        @endif
    </x-slot:actions>

    @if($openJob)
        @if($openJob->technicalReport)
            <div class="card mb-4"><div class="card-body">
                @include('livewire.engineering.reports.partials.technical-report-document', ['job' => $openJob, 'report' => $openJob->technicalReport])
            </div></div>
        @endif

        @if($canEdit)
            <div class="card eng-no-print">
                <div class="card-header"><h6 class="mb-0">{{ $openJob->technicalReport ? 'Edit the report' : 'Write the report' }} <span class="text-muted fw-normal small">· equipment details are taken from the inventory</span></h6></div>
                <form wire:submit="save" class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date reported <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('form.date_reported') is-invalid @enderror" wire:model="form.date_reported">
                        </div>
                        @foreach([
                            'problem_background' => ['Problem background', 'When it started and the current status', true],
                            'work_done' => ['What you did', 'Troubleshooting, diagnosis, maintenance', true],
                            'equipment_requirement' => ['What the equipment requires now', 'What should be done', true],
                            'engineer_needs' => ['What you need', 'e.g. external help for maintenance', false],
                        ] as $field => [$label, $placeholder, $required])
                            <div class="col-12">
                                <label class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
                                <textarea class="form-control @error('form.'.$field) is-invalid @enderror" rows="3" wire:model="form.{{ $field }}" placeholder="{{ $placeholder }}"></textarea>
                                @error('form.'.$field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endforeach
                        @foreach(['current_value' => 'Cost of the equipment now', 'new_purchase_cost' => 'Expected cost to buy new', 'external_expert_cost' => 'Cost to bring an external expert'] as $field => $label)
                            <div class="col-md-4">
                                <label class="form-label">{{ $label }} ({{ config('engineering.currency') }})</label>
                                <input type="number" min="0" step="any" class="form-control @error('form.'.$field) is-invalid @enderror" wire:model="form.{{ $field }}">
                            </div>
                        @endforeach
                        <div class="col-12">
                            <label class="form-label">Comment to management: what needs to be done <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('form.comment_to_management') is-invalid @enderror" rows="3" wire:model="form.comment_to_management"></textarea>
                            @error('form.comment_to_management') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="text-end mt-3"><button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save report</button></div>
                </form>
            </div>
        @elseif(! $openJob->technicalReport)
            <div class="alert alert-info">No technical report has been written for this job. It is written by the assigned engineer.</div>
        @endif
    @else
        @if($candidates->isNotEmpty())
            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap align-items-center gap-2">
                    <span class="fw-semibold">Write a report for one of your jobs:</span>
                    <select class="form-select" style="max-width: 420px;" wire:change="open($event.target.value)">
                        <option value="">Select a job</option>
                        @foreach($candidates as $candidate)<option value="{{ $candidate->id }}">{{ $candidate->number }} · {{ $candidate->equipmentName() }}</option>@endforeach
                    </select>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header"><input type="search" class="form-control" placeholder="Search MRV number or equipment" wire:model.live.debounce.400ms="search"></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>S/N</th><th>Date reported</th><th>MRV</th><th>Equipment</th><th>Department</th><th>Reported by</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr wire:key="report-{{ $report->id }}">
                                <td>{{ $reports->firstItem() + $loop->index }}</td>
                                <td>{{ Eng::date($report->date_reported) }}</td>
                                <td class="fw-semibold">{{ $report->job->number }}</td>
                                <td>{{ $report->job->equipmentName() }} <span class="text-muted small">{{ $report->job->equipment?->code }}</span></td>
                                <td>{{ $report->job->requisition->department?->name ?? '—' }}</td>
                                <td>{{ $report->reportedBy?->full_name }}</td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary" wire:click="open('{{ $report->job_id }}')">View report</button></td>
                            </tr>
                        @empty
                            <x-engineering.empty colspan="7" icon="fa-file-signature" text="No technical reports yet. An engineer writes one from a job." />
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())<div class="card-footer">{{ $reports->links() }}</div>@endif
        </div>
    @endif
</x-engineering.page>
