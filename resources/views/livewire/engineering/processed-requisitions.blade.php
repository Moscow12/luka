@php use App\Support\Engineering\Eng; use App\Support\Engineering\Lookups; @endphp

<x-engineering.page title="Processed requisitions" subtitle="Rate the finished work. Your rating closes the requisition.">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="card mb-3">
        <div class="card-body">
            <input type="search" class="form-control" placeholder="Search MRV number, equipment or problem" wire:model.live.debounce.400ms="search">
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>S/N</th>
                        <th>Requisition</th>
                        <th>Equipment</th>
                        <th>Location</th>
                        <th>Engineer</th>
                        <th>Completed</th>
                        <th class="text-center">TAT</th>
                        <th>Final status</th>
                        <th>My rating</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        <tr wire:key="job-{{ $job->id }}" class="{{ $job->is_rework ? 'eng-row-rework' : '' }}">
                            <td>{{ $jobs->firstItem() + $loop->index }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('engineering.jobs.summary', $job) }}" class="fw-semibold text-decoration-none">{{ $job->number }}</a>
                                @if($job->is_rework)<span class="badge bg-danger ms-1">RE-WORK</span>@endif
                                <div class="small text-muted">{{ Eng::dt($job->requisition->requested_at) }}</div>
                            </td>
                            <td>{{ $job->equipmentName() }}</td>
                            <td>
                                {{ $job->requisition->locationLabel() }}
                                <div class="small text-muted">{{ $job->requisition->department?->name }}</div>
                            </td>
                            <td>{{ Eng::name($job->engineer) }}</td>
                            <td class="text-nowrap">{{ Eng::dt($job->completed_at) }}</td>
                            <td class="text-center">{{ $job->tatDays() !== null ? number_format($job->tatDays(), 2).' d' : '—' }}</td>
                            <td>{{ Lookups::label(Lookups::FINAL_STATUSES, $job->final_status) }}</td>
                            <td>
                                @if($job->currentFeedback)
                                    <span class="badge {{ $job->currentFeedback->rating->badgeClass() }}">{{ $job->currentFeedback->rating->label() }}</span>
                                @else
                                    <button type="button" class="btn btn-sm btn-primary eng-blink" wire:click="openRating('{{ $job->id }}')"><i class="fa-solid fa-star me-1"></i>Rate now</button>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="openRework('{{ $job->id }}')" title="Resend for rework"><i class="fa-solid fa-rotate-left"></i></button>
                                <a href="{{ route('engineering.jobs.summary', $job) }}" class="btn btn-sm btn-outline-secondary" title="Preview completed MRV"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="10" text="No finished work yet. Completed requisitions appear here for your rating." />
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())
            <div class="card-footer">{{ $jobs->links() }}</div>
        @endif
    </div>

    {{-- Feedback form --}}
    @if($ratingJob)
        <x-engineering.modal :title="'Feedback on completed work · '.$ratingJob->number" close="closeRating" size="modal-lg">
            <form wire:submit="saveFeedback">
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <x-engineering.detail label="Equipment">{{ $ratingJob->equipmentName() }}</x-engineering.detail>
                        <x-engineering.detail label="Location">{{ $ratingJob->requisition->locationLabel() }}</x-engineering.detail>
                        <x-engineering.detail label="Engineer">{{ Eng::name($ratingJob->engineer) }} ({{ $ratingJob->section->name }})</x-engineering.detail>
                        <x-engineering.detail label="Completed">{{ Eng::dt($ratingJob->completed_at) }}</x-engineering.detail>
                        <x-engineering.detail label="Final status">{{ Lookups::label(Lookups::FINAL_STATUSES, $ratingJob->final_status) }}</x-engineering.detail>
                        <x-engineering.detail label="TAT">{{ number_format((float) $ratingJob->tatDays(), 2) }} days</x-engineering.detail>
                    </div>
                    @if($ratingJob->recommendation)
                        <div class="alert alert-info"><strong>Engineer's recommendation:</strong> {{ $ratingJob->recommendation }}</div>
                    @endif

                    <label class="form-label fw-semibold">How satisfied are you with the solution? <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-3">
                        @foreach($ratings as $option)
                            <div class="col-6 col-md">
                                <input type="radio" class="btn-check" id="rating-{{ $option->value }}" value="{{ $option->value }}" wire:model.live="rating">
                                <label class="btn btn-outline-primary w-100 h-100 d-flex align-items-center justify-content-center" for="rating-{{ $option->value }}">{{ $option->label() }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('rating') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                    @php $mustExplain = $rating && \App\Enums\Engineering\SatisfactionRating::from($rating)->requiresComment(); @endphp
                    <label class="form-label">Comment @if($mustExplain)<span class="text-danger">* required for this rating</span>@endif</label>
                    <textarea class="form-control" rows="3" wire:model="comment" placeholder="What went well, or what is still wrong"></textarea>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('engineering.jobs.summary', $ratingJob) }}" class="btn btn-link me-auto">Preview completed MRV</a>
                    <button type="button" class="btn btn-secondary" wire:click="closeRating">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save feedback</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif

    {{-- Resend for rework --}}
    @if($reworkJob)
        <x-engineering.modal :title="'Send '.$reworkJob->number.' back for rework?'" close="closeRework" header="bg-danger text-white">
            <form wire:submit="resend">
                <div class="modal-body">
                    <p>
                        The job goes back to <strong>{{ Eng::name($reworkJob->engineer) }}</strong>.
                        The requisition number stays the same and it will be permanently marked <span class="badge bg-danger">RE-WORK</span>.
                    </p>
                    <label class="form-label">What still needs to be done? <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('reworkReason') is-invalid @enderror" rows="3" wire:model="reworkReason"></textarea>
                    @error('reworkReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeRework">No, leave it closed</button>
                    <button type="submit" class="btn btn-danger">Yes, resend</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif
</x-engineering.page>
