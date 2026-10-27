<div>
    <div class="row mb-4">
        <div class="col-12 d-md-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">New Ticket</h4>
                <p class="text-muted mb-0">Create a request and assign it in one step</p>
            </div>
            <a href="{{ route('tickets.mine') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>My Tickets
            </a>
        </div>
    </div>

    @include('livewire.tickets.partials.flash')

    <form wire:submit="save">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-ticket me-2"></i>Request details</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" wire:model="subject" maxlength="150" placeholder="Short, explicit title">
                            <div class="form-text text-end">{{ strlen($subject) }}/150</div>
                            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <x-rich-editor model="description" placeholder="Describe the task and what 'done' looks like..." height="200px" />
                            @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-1">
                            <label class="form-label">Attachments</label>
                            <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" wire:model="attachments" multiple>
                            <div class="form-text">PDF, images, Office documents, up to 25 MB each.</div>
                            <div wire:loading wire:target="attachments" class="small text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Uploading...</div>
                            @error('attachments.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @if(count($attachments))
                                <ul class="list-group list-group-flush mt-2">
                                    @foreach($attachments as $i => $file)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="fa-solid fa-paperclip me-2 text-muted"></i>{{ $file->getClientOriginalName() }}</span>
                                            <button type="button" class="btn btn-sm btn-link text-danger" wire:click="removeAttachment({{ $i }})"><i class="fa-solid fa-xmark"></i></button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-sliders me-2"></i>Classification</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" wire:model.live="category_id">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if($categories->isEmpty())
                                <div class="form-text text-warning">No categories configured yet. Ask an administrator to add some.</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="priority">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" wire:model="due_date" min="{{ now()->toDateString() }}">
                            @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Visibility</label>
                            <select class="form-select" wire:model="visibility">
                                <option value="participants">Restricted to participants</option>
                                <option value="department">Visible to the assigned department</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-user-check me-2"></i>Assignment</h6></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror" wire:model.live="department_id">
                                <option value="">Select department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigned officer</label>
                            <select class="form-select @error('assignee_employee_id') is-invalid @enderror" wire:model.live="assignee_employee_id" @disabled(! $department_id)>
                                <option value="">Department queue (supervisor delegates)</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->getFullName() }} @if($employee->employee_no)({{ $employee->employee_no }})@endif</option>
                                @endforeach
                            </select>
                            @error('assignee_employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @if($assignee_employee_id)
                            <div>
                                <label class="form-label">Link to duty <span class="text-muted small">(optional)</span></label>
                                <select class="form-select @error('employee_assigned_duty_id') is-invalid @enderror" wire:model="employee_assigned_duty_id">
                                    <option value="">Not linked</option>
                                    @foreach($duties as $duty)
                                        <option value="{{ $duty->id }}">{{ $duty->duty_name }} ({{ str_replace('_', ' ', $duty->measurement_type) }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Closing this ticket will count towards the selected duty's achievement.</div>
                                @error('employee_assigned_duty_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save"><i class="fa-solid fa-paper-plane me-1"></i>Create ticket</span>
                        <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1"></span>Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
