@php use App\Livewire\Engineering\Setup; use App\Support\Engineering\Eng; @endphp

<x-engineering.page title="Engineering setup" subtitle="Sections, staff, problem categories, KPI weights and oxygen plants">
    <ul class="nav nav-pills mb-3 flex-nowrap overflow-auto">
        @foreach(Setup::TABS as $key => $label)
            <li class="nav-item"><button type="button" class="nav-link text-nowrap {{ $tab === $key ? 'active' : '' }}" wire:click="setTab('{{ $key }}')">{{ $label }}</button></li>
        @endforeach
    </ul>

    @if(in_array($tab, ['sections', 'categories']))
        <div class="row g-4">
            {{-- Section list --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="list-group list-group-flush">
                        @foreach($sections as $item)
                            <button type="button" wire:click="$set('sectionId', '{{ $item->id }}')" wire:key="section-{{ $item->id }}"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $sectionId === $item->id ? 'active' : '' }}">
                                <span><i class="fa-solid {{ $item->icon ?? 'fa-screwdriver-wrench' }} fa-fw me-2"></i>{{ $item->name }}
                                    @if($item->status !== 'active')<span class="badge bg-secondary ms-1">Inactive</span>@endif
                                </span>
                                <span class="badge rounded-pill {{ $sectionId === $item->id ? 'bg-light text-dark' : 'bg-secondary' }}">{{ $item->staff_count }}</span>
                            </button>
                        @endforeach
                    </div>
                    @if($tab === 'sections')
                        <form wire:submit="addSection" class="card-footer">
                            <div class="row g-2">
                                <div class="col-5"><input type="text" class="form-control form-control-sm text-uppercase @error('newSection.code') is-invalid @enderror" placeholder="CODE" wire:model="newSection.code"></div>
                                <div class="col-7"><input type="text" class="form-control form-control-sm @error('newSection.name') is-invalid @enderror" placeholder="Section name" wire:model="newSection.name"></div>
                                <div class="col-12"><button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fa-solid fa-plus me-1"></i>Add section</button></div>
                            </div>
                            @error('newSection.code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </form>
                    @endif
                </div>
            </div>

            <div class="col-lg-8">
                @if(! $section)
                    <div class="card"><div class="card-body text-muted">Run the engineering seeder or add a section to begin.</div></div>
                @elseif($tab === 'sections')
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $section->name }} · staff</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggleSection('{{ $section->id }}')">{{ $section->status === 'active' ? 'Deactivate section' : 'Activate section' }}</button>
                        </div>
                        <div class="card-body">
                            <label class="form-label">Add staff to this section</label>
                            <input type="search" class="form-control" placeholder="Search employees by name or number" wire:model.live.debounce.300ms="staffSearch">
                            @if($staffMatches->isNotEmpty())
                                <div class="list-group mt-1 shadow-sm">
                                    @foreach($staffMatches as $employee)
                                        <button type="button" class="list-group-item list-group-item-action" wire:click="addStaff('{{ $employee->id }}')" wire:key="match-{{ $employee->id }}">
                                            {{ Eng::name($employee) }} <span class="text-muted small">{{ $employee->employee_no }} · {{ $employee->department?->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            <div class="form-text">Only staff listed here can be assigned this section's jobs. The head of section can assign and re-assign within it.</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light"><tr><th>Staff</th><th>Phone</th><th>Account</th><th class="text-center">Head of section</th><th></th></tr></thead>
                                <tbody>
                                    @forelse($section->staff as $employee)
                                        <tr wire:key="staff-{{ $employee->id }}">
                                            <td>{{ Eng::name($employee) }}</td>
                                            <td>{{ $employee->phone ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $employee->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $employee->status === 'Active' ? 'Active' : 'Frozen · '.$employee->status }}</span>
                                                @unless($employee->user_id)<span class="badge bg-warning text-dark" title="This employee has no user account and cannot log in">No login</span>@endunless
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block mb-0">
                                                    <input class="form-check-input" type="checkbox" role="switch" @checked($employee->pivot->is_head) wire:click="toggleHead('{{ $employee->id }}')">
                                                </div>
                                            </td>
                                            <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger" wire:click="removeStaff('{{ $employee->id }}')" wire:confirm="Remove {{ Eng::name($employee) }} from {{ $section->name }}?"><i class="fa-solid fa-user-minus"></i></button></td>
                                        </tr>
                                    @empty
                                        <x-engineering.empty colspan="5" icon="fa-people-group" text="No staff in this section yet." />
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">{{ $section->name }} · problem categories</h6></div>
                        <form wire:submit="addCategory" class="card-body border-bottom">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-7"><input type="text" class="form-control @error('newCategory.name') is-invalid @enderror" placeholder="New category" wire:model="newCategory.name"></div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="category-emergency" wire:model="newCategory.emergency">
                                        <label class="form-check-label" for="category-emergency">Emergency criterion</label>
                                    </div>
                                </div>
                                <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Add</button></div>
                            </div>
                            <div class="form-text">Choosing an emergency criterion on a request sets its priority to Emergency automatically.</div>
                        </form>
                        <ul class="list-group list-group-flush">
                            @forelse($categories as $category)
                                <li class="list-group-item d-flex justify-content-between align-items-center {{ $category->status !== 'active' ? 'text-muted' : '' }}" wire:key="category-{{ $category->id }}">
                                    <span>
                                        @if($category->isEmergency())<span class="badge bg-danger me-1">{{ $category->emergency_code }}</span>@endif
                                        {{ $category->name }}
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggleCategory('{{ $category->id }}')">{{ $category->status === 'active' ? 'Disable' : 'Enable' }}</button>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No categories for this section yet.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @elseif($tab === 'weights')
        <div class="card" style="max-width: 720px;">
            <div class="card-header"><h6 class="mb-0">Individual KPI weights</h6></div>
            <form wire:submit="saveWeights" class="card-body">
                <p class="text-muted">The weights must total 100%. A KPI that does not apply to a person in a week is left out and the rest are re-normalised.</p>
                @foreach(Setup::WEIGHT_LABELS as $key => $label)
                    <div class="row g-2 align-items-center mb-2">
                        <label class="col-8 col-form-label" for="weight-{{ $key }}">{{ $label }}</label>
                        <div class="col-4">
                            <div class="input-group">
                                <input type="number" min="0" max="100" step="any" id="weight-{{ $key }}" class="form-control @error('weights.'.$key) is-invalid @enderror" wire:model.live.debounce.300ms="weights.{{ $key }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                    <span class="fw-semibold {{ $weightTotal == 100 ? 'text-success' : 'text-danger' }}">Total: {{ $weightTotal }}%</span>
                    <button type="submit" class="btn btn-primary" @disabled($weightTotal != 100)>Save weights</button>
                </div>
                @error('weights') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </form>
        </div>
    @else
        <div class="card" style="max-width: 860px;">
            <div class="card-header"><h6 class="mb-0">Oxygen plants</h6></div>
            <form wire:submit="addPlant" class="card-body border-bottom">
                <div class="row g-2">
                    <div class="col-md-5"><input type="text" class="form-control @error('newPlant.name') is-invalid @enderror" placeholder="Plant name" wire:model="newPlant.name"></div>
                    <div class="col-md-5">
                        <select class="form-select" wire:model="newPlant.responsible_employee_id">
                            <option value="">Responsible staff (optional)</option>
                            @foreach($engineers as $engineer)<option value="{{ $engineer->id }}">{{ $engineer->first_name }} {{ $engineer->last_name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Add plant</button></div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Plant</th><th>Responsible staff</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($plants as $plant)
                            <tr wire:key="plant-{{ $plant->id }}">
                                <td class="fw-semibold">{{ $plant->name }}</td>
                                <td>
                                    <select class="form-select form-select-sm" wire:change="setPlantResponsible('{{ $plant->id }}', $event.target.value)">
                                        <option value="">Nobody</option>
                                        @foreach($engineers as $engineer)<option value="{{ $engineer->id }}" @selected($plant->responsible_employee_id === $engineer->id)>{{ $engineer->first_name }} {{ $engineer->last_name }}</option>@endforeach
                                    </select>
                                </td>
                                <td><span class="badge {{ $plant->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($plant->status) }}</span></td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" wire:click="togglePlant('{{ $plant->id }}')">{{ $plant->status === 'active' ? 'Deactivate' : 'Activate' }}</button></td>
                            </tr>
                        @empty
                            <x-engineering.empty colspan="4" icon="fa-lungs" text="No oxygen plants yet." />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer small text-muted">Purity readings are recorded under Reports → Individual KPI → Oxygen purity, and count towards the responsible person's KPI.</div>
        </div>
    @endif
</x-engineering.page>
