<x-engineering.page title="Request engineering service" subtitle="Tell engineering what is wrong, where it is and how urgent it is">
    <x-slot:actions>
        <a href="{{ route('engineering.requisitions') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-list-check me-1"></i>My requisitions</a>
    </x-slot:actions>

    @if($submitted)
        {{-- Confirmation --}}
        <div class="card border-success">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-circle-check text-success fa-3x mb-3"></i>
                <h4 class="mb-1">Requisition {{ $submitted->number }} submitted</h4>
                <p class="text-muted mb-3">
                    {{ $submitted->jobs->count() }} {{ Str::plural('item', $submitted->jobs->count()) }} sent to the engineering helpdesk
                    @if($submitted->isEmergency()) as an <span class="badge bg-danger">Emergency</span> @endif
                    on {{ \App\Support\Engineering\Eng::dt($submitted->requested_at) }}.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('engineering.requisitions') }}" class="btn btn-primary"><i class="fa-solid fa-location-crosshairs me-1"></i>Track it in My requisitions</a>
                    <button type="button" class="btn btn-outline-secondary" wire:click="startAnother">Raise another request</button>
                </div>
            </div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                {{-- 1. Section --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center gap-2">
                        <span class="eng-step {{ $sectionId ? 'bg-success text-white' : 'bg-primary text-white' }}">1</span>
                        <h6 class="mb-0">Which section should handle it?</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($sections as $section)
                                <div class="col-4" wire:key="section-{{ $section->id }}">
                                    <button type="button" wire:click="selectSection('{{ $section->id }}')"
                                            class="card eng-tile w-100 h-100 text-center py-3 px-1 {{ $sectionId === $section->id ? 'border-primary border-2 bg-primary-subtle' : '' }}">
                                        <i class="fa-solid {{ $section->icon ?? 'fa-screwdriver-wrench' }} fa-lg mb-2 {{ $sectionId === $section->id ? 'text-primary' : 'text-muted' }}"></i>
                                        <span class="small fw-semibold d-block">{{ $section->name }}</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        @error('sectionId') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>
                </div>

                @if($sectionId)
                    {{-- 2 & 3. Nature of work and category --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center gap-2">
                            <span class="eng-step {{ $categoryId && $emergency !== null ? 'bg-success text-white' : 'bg-primary text-white' }}">2</span>
                            <h6 class="mb-0">What kind of problem is it?</h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label">Problem category <span class="text-danger">*</span></label>
                            <select class="form-select @error('categoryId') is-invalid @enderror" wire:model.live="categoryId">
                                <option value="">Select the category that best describes the problem</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->isEmergency() ? '⚠ ' : '' }}{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('categoryId') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            <label class="form-label mt-3">Nature of work <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" wire:click="setEmergency(false)" @disabled($this->autoEmergency)
                                            class="btn w-100 py-3 {{ $emergency === false ? 'btn-primary' : 'btn-outline-primary' }}">
                                        <i class="fa-solid fa-calendar-check me-1"></i>Routine work
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" wire:click="setEmergency(true)"
                                            class="btn w-100 py-3 {{ $emergency === true ? 'btn-danger' : 'btn-outline-danger' }}">
                                        <i class="fa-solid fa-bolt me-1"></i>Emergency work
                                    </button>
                                </div>
                            </div>
                            @error('emergency') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                            @if($this->autoEmergency)
                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    This category is on the emergency list, so the request is classified <strong>Emergency</strong> automatically.
                                </div>
                            @elseif($this->needsJustification)
                                <div class="mt-3">
                                    <label class="form-label">Why is this an emergency? <span class="text-danger">*</span></label>
                                    <textarea class="form-control" rows="2" wire:model="justification" placeholder="At least 10 characters. The helpdesk will read this."></textarea>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 4. Items --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="eng-step {{ count($items) ? 'bg-success text-white' : 'bg-primary text-white' }}">3</span>
                                <h6 class="mb-0">What needs attention?</h6>
                            </div>
                            <span class="badge bg-light text-dark border">{{ count($items) }} / {{ $maxItems }} items</span>
                        </div>
                        <div class="card-body">
                            @foreach($items as $index => $item)
                                <div class="border rounded p-3 mb-2" wire:key="item-{{ $index }}">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div>
                                            <span class="badge bg-secondary me-1">{{ $index + 1 }}</span>
                                            <span class="fw-semibold">{{ $item['equipment_label'] }}</span>
                                            @unless($item['equipment_id'])<span class="badge bg-light text-dark border ms-1">Not in inventory</span>@endunless
                                            <div class="text-muted small mt-1">{{ $item['problem_description'] }}</div>
                                            @if($item['warning'])
                                                <div class="text-warning small mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $item['warning'] }} You can still submit.</div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link text-danger" wire:click="removeItem({{ $index }})" title="Remove item"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                            @endforeach

                            @if(count($items) < $maxItems)
                                <div class="bg-light rounded p-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label">Equipment or area <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            <input type="text" class="form-control @error('equipmentSearch') is-invalid @enderror"
                                                   wire:model.live.debounce.300ms="equipmentSearch" @readonly($draftEquipmentId)
                                                   placeholder="Search by name, ID code or serial number, or type the area (e.g. Ward 3 toilet)">
                                            @if($draftEquipmentId)
                                                <button type="button" class="btn btn-outline-secondary" wire:click="clearEquipment" title="Change"><i class="fa-solid fa-xmark"></i></button>
                                            @endif
                                        </div>
                                        @error('equipmentSearch') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                        @if($matches->isNotEmpty())
                                            <div class="list-group mt-1 shadow-sm">
                                                @foreach($matches as $equipment)
                                                    <button type="button" class="list-group-item list-group-item-action" wire:click="pickEquipment('{{ $equipment->id }}')" wire:key="match-{{ $equipment->id }}">
                                                        <span class="fw-semibold">{{ $equipment->name }}</span>
                                                        <span class="text-muted small ms-1">{{ $equipment->code }} · S/N {{ $equipment->serial_number }} · {{ $equipment->room?->name }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @elseif(! $draftEquipmentId && mb_strlen(trim($equipmentSearch)) >= 2)
                                            <div class="form-text">Not found in the inventory. It will be sent as typed and flagged for the inventory team.</div>
                                        @endif
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Describe the problem <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('draftDescription') is-invalid @enderror" rows="3" wire:model="draftDescription"
                                                  placeholder="What happens, since when, any alarm or error shown"></textarea>
                                        @error('draftDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" wire:click="saveItem" wire:loading.attr="disabled" wire:target="saveItem">
                                        <i class="fa-solid fa-plus me-1"></i>Save item
                                    </button>
                                </div>
                            @endif
                            @error('items') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif
            </div>

            {{-- Requester + submit --}}
            <div class="col-lg-4">
                <div class="card position-sticky" style="top: 1rem;">
                    <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-user me-2"></i>Your details</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="fw-semibold">{{ auth()->user()->full_name }}</div>
                            <div class="text-muted small">Filled in from your profile. Change the phone if engineering should call the ward instead.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" wire:model="phone">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-select" wire:model.live="departmentId">
                                <option value="">Select department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room</label>
                            <select class="form-select" wire:model="roomId" @disabled($rooms->isEmpty())>
                                <option value="">{{ $rooms->isEmpty() ? 'No rooms registered for this department' : 'Select room' }}</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Place / floor</label>
                            <input type="text" class="form-control @error('locationText') is-invalid @enderror" wire:model="locationText" placeholder="e.g. Block B, 2nd floor, bed 4">
                            @error('locationText') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="button" class="btn btn-lg w-100 {{ $emergency ? 'btn-danger' : 'btn-primary' }}" wire:click="submit"
                                wire:loading.attr="disabled" wire:target="submit" @disabled(! count($items))>
                            <span wire:loading.remove wire:target="submit"><i class="fa-solid fa-paper-plane me-1"></i>Submit requisition</span>
                            <span wire:loading wire:target="submit"><span class="spinner-border spinner-border-sm me-1"></span>Submitting…</span>
                        </button>
                        <div class="form-text text-center">The MRV number is generated when you submit.</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-engineering.page>
