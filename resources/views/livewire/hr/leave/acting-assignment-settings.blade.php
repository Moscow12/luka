<div>
    <x-pages.breadcrumn title="Acting Assignment Settings"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Leave Management', 'url' => route('leave.leavemanagement')],
        ['label' => 'Acting Assignment Settings']
        ]">
    </x-pages.breadcrumn>

    <div class="row">
        <div class="col-12">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session()->has('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <x-pages.card title="Acting Assignment Configuration">
                <form wire:submit.prevent="save">
                    <!-- Enable Acting Assignments -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model.defer="acting_assignment_enabled" id="acting_assignment_enabled">
                            <label class="form-check-label" for="acting_assignment_enabled">
                                <strong>Enable Acting Assignments</strong>
                                <br>
                                <small class="text-muted">Allow employees to assign acting roles when requesting leave</small>
                            </label>
                        </div>
                    </div>

                    @if($acting_assignment_enabled)
                        <div class="border-start border-primary border-3 ps-4 mb-4">
                            <!-- Mandatory Acting Assignment -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="acting_assignment_mandatory" id="acting_assignment_mandatory">
                                    <label class="form-check-label" for="acting_assignment_mandatory">
                                        <strong>Require Acting Assignment for Extended Leave</strong>
                                        <br>
                                        <small class="text-muted">Make acting assignments mandatory for leave requests exceeding minimum days</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Minimum Days -->
                            <div class="mb-3">
                                <label for="acting_assignment_min_days" class="form-label">
                                    Minimum Leave Days to Require Acting Assignment
                                </label>
                                <input type="number" class="form-control" id="acting_assignment_min_days"
                                    wire:model.defer="acting_assignment_min_days" min="1" max="365">
                                <small class="text-muted">Acting assignment will be required/suggested for leaves of this duration or longer</small>
                                @error('acting_assignment_min_days')
                                    <span class="text-danger d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Auto Approve -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="acting_assignment_auto_approve" id="acting_assignment_auto_approve">
                                    <label class="form-check-label" for="acting_assignment_auto_approve">
                                        <strong>Auto-Approve Acting Assignments</strong>
                                        <br>
                                        <small class="text-muted">Automatically approve acting assignments when the leave request is approved</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Notify Acting Employee -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="acting_assignment_notify_employee" id="acting_assignment_notify_employee">
                                    <label class="form-check-label" for="acting_assignment_notify_employee">
                                        <strong>Notify Acting Employee</strong>
                                        <br>
                                        <small class="text-muted">Send notification to acting employee when assignment is created/approved</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Designation Filter -->
                            <div class="mb-3">
                                <label for="acting_assignment_designations" class="form-label">
                                    Designations Requiring Acting Assignment
                                </label>
                                <select class="form-select" wire:model.defer="acting_assignment_designations" multiple size="5">
                                    <option value="">All Designations (Leave empty for all)</option>
                                    @foreach($allDesignations as $designation)
                                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty to apply to all designations. Hold Ctrl/Cmd to select multiple.</small>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> Save Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="resetToDefaults">
                            <i class="fa fa-undo me-1"></i> Reset to Defaults
                        </button>
                    </div>
                </form>
            </x-pages.card>
        </div>
    </div>
</div>
