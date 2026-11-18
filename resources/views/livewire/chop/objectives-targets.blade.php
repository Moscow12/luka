<div>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h5 class="mb-1">Objectives &amp; Targets</h5>
            <p class="text-muted small mb-0">Objectives carry over between years. Each financial year has its own targets, and every planned intervention (activity) links to one target.</p>
        </div>
        @if($canManage)
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary btn-sm" wire:click="openObjectiveModal">
                    <i class="fa-solid fa-plus me-1"></i>Add Objective
                </button>
                <button class="btn btn-outline-primary btn-sm" wire:click="openTargetModal" @disabled($allObjectives->isEmpty())>
                    <i class="fa-solid fa-bullseye me-1"></i>Add Target
                </button>
                <button class="btn btn-outline-secondary btn-sm" wire:click="openCopyModal" @disabled(! $filterFinancialYear)>
                    <i class="fa-solid fa-copy me-1"></i>Copy targets from another year
                </button>
            </div>
        @endif
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label small text-muted">Financial year (targets)</label>
            <select class="form-select" wire:model.live="filterFinancialYear">
                <option value="">All years</option>
                @foreach($financialYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label small text-muted">Search</label>
            <input type="search" class="form-control" wire:model.live.debounce.400ms="search" placeholder="Search objectives or targets...">
        </div>
    </div>

    @if($unlinkedActivities > 0)
        <div class="alert alert-warning py-2 small">
            <i class="fa-solid fa-link-slash me-1"></i>
            {{ $unlinkedActivities }} activity(ies) in this financial year have no target yet. Assign them in the <strong>Activities</strong> tab so they appear under the right objective in the cost analysis.
        </div>
    @endif

    @forelse($objectives as $objective)
        <div class="card shadow-sm mb-3" wire:key="objective-{{ $objective->id }}">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <div class="fw-semibold">
                        @if($objective->code)
                            <span class="badge bg-primary me-1">{{ $objective->code }}</span>
                        @endif
                        {{ $objective->name }}
                        @if($objective->status === 'inactive')
                            <span class="badge bg-secondary ms-1">Inactive</span>
                        @endif
                    </div>
                    @if($objective->description)
                        <div class="small text-muted">{{ $objective->description }}</div>
                    @endif
                </div>
                @if($canManage)
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" wire:click="openTargetModal(null, {{ Js::from($objective->id) }})" title="Add target to this objective">
                            <i class="fa-solid fa-bullseye"></i> Add target
                        </button>
                        <button class="btn btn-outline-secondary" wire:click="openObjectiveModal({{ Js::from($objective->id) }})" title="Edit objective">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger" wire:click="deleteObjective({{ Js::from($objective->id) }})"
                                wire:confirm="Delete this objective?" title="Delete objective">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">S/N</th>
                                <th>Target</th>
                                @unless($filterFinancialYear)
                                    <th style="width: 110px;">Year</th>
                                @endunless
                                <th class="text-center" style="width: 100px;">Activities</th>
                                <th class="text-end" style="width: 160px;">Planned (TZS)</th>
                                <th class="text-center" style="width: 90px;">Status</th>
                                @if($canManage)
                                    <th style="width: 90px;"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($objective->targets as $target)
                                <tr wire:key="target-{{ $target->id }}">
                                    <td class="text-center">{{ $target->code ?: $loop->iteration }}</td>
                                    <td>{{ $target->description }}</td>
                                    @unless($filterFinancialYear)
                                        <td>{{ $target->financialYear?->name }}</td>
                                    @endunless
                                    <td class="text-center">
                                        <span class="badge {{ $target->activities_count ? 'bg-info' : 'bg-light text-muted' }}">{{ $target->activities_count }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format((float) $target->activities_sum_planned_amount, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $target->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($target->status) }}</span>
                                    </td>
                                    @if($canManage)
                                        <td class="text-end text-nowrap">
                                            <button class="btn btn-sm btn-link p-0 me-2" wire:click="openTargetModal({{ Js::from($target->id) }})" title="Edit target">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-link text-danger p-0" wire:click="deleteTarget({{ Js::from($target->id) }})"
                                                    wire:confirm="Delete this target?" title="Delete target">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted small py-3">
                                        No targets {{ $filterFinancialYear ? 'for this financial year' : 'yet' }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-5 border rounded">
            <i class="fa-solid fa-bullseye fa-3x mb-3 d-block opacity-50"></i>
            {{ trim($search) !== '' ? 'No objectives or targets match your search.' : 'No objectives yet. Add one, e.g. "Improve access, quality and equitable social services delivery".' }}
        </div>
    @endforelse

    {{-- Objective modal --}}
    @if($showObjectiveModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <form class="modal-content" wire:submit="saveObjective">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $objectiveId ? 'Edit Objective' : 'Add Objective' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showObjectiveModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Objective <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('objectiveName') is-invalid @enderror" rows="2" wire:model="objectiveName"
                                      placeholder="e.g. Improve access, quality and equitable social services delivery"></textarea>
                            @error('objectiveName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control @error('objectiveCode') is-invalid @enderror" wire:model="objectiveCode" placeholder="e.g. C">
                                @error('objectiveCode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Order</label>
                                <input type="number" min="0" class="form-control @error('objectiveSortOrder') is-invalid @enderror" wire:model="objectiveSortOrder">
                                @error('objectiveSortOrder') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" wire:model="objectiveStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="2" wire:model="objectiveDescription"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showObjectiveModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">{{ $objectiveId ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Target modal --}}
    @if($showTargetModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <form class="modal-content" wire:submit="saveTarget">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $targetId ? 'Edit Target' : 'Add Target' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showTargetModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Objective <span class="text-danger">*</span></label>
                            <select class="form-select @error('targetObjectiveId') is-invalid @enderror" wire:model="targetObjectiveId">
                                <option value="">Select objective</option>
                                @foreach($allObjectives as $o)
                                    <option value="{{ $o->id }}">{{ $o->code ? $o->code.'. ' : '' }}{{ \Illuminate\Support\Str::limit($o->name, 90) }}{{ $o->status === 'inactive' ? ' (inactive)' : '' }}</option>
                                @endforeach
                            </select>
                            @error('targetObjectiveId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('targetDescription') is-invalid @enderror" rows="3" wire:model="targetDescription"
                                      placeholder="e.g. Shortage of medicines, medical equipment, medical and diagnostic supplies reduced from 5% to 0% by June 2026"></textarea>
                            @error('targetDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Financial year <span class="text-danger">*</span></label>
                                <select class="form-select @error('targetFinancialYearId') is-invalid @enderror" wire:model="targetFinancialYearId">
                                    <option value="">Select year</option>
                                    @foreach($financialYears as $fy)
                                        <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                    @endforeach
                                </select>
                                @error('targetFinancialYearId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">S/N</label>
                                <input type="text" class="form-control @error('targetCode') is-invalid @enderror" wire:model="targetCode" placeholder="auto">
                                @error('targetCode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Order</label>
                                <input type="number" min="0" class="form-control @error('targetSortOrder') is-invalid @enderror" wire:model="targetSortOrder">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" wire:model="targetStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showTargetModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">{{ $targetId ? 'Update' : 'Save' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Copy targets modal --}}
    @if($showCopyModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <form class="modal-content" wire:submit="copyTargets">
                    <div class="modal-header">
                        <h5 class="modal-title">Copy targets</h5>
                        <button type="button" class="btn-close" wire:click="$set('showCopyModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            Copies every target of the chosen year into
                            <strong>{{ $financialYears->firstWhere('id', $filterFinancialYear)?->name }}</strong>,
                            under the same objectives. Targets already there are skipped. Edit the copies afterwards to update figures and "by June" dates.
                        </p>
                        @error('filterFinancialYear') <div class="alert alert-danger py-2 small">{{ $message }}</div> @enderror
                        <label class="form-label">Copy from <span class="text-danger">*</span></label>
                        <select class="form-select @error('copyFromYearId') is-invalid @enderror" wire:model="copyFromYearId">
                            <option value="">Select year</option>
                            @foreach($financialYears as $fy)
                                @if($fy->id !== $filterFinancialYear)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('copyFromYearId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCopyModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Copy targets</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
