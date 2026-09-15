<div>
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">SMS API Settings</h5>
                <p class="text-muted mb-0">Configure SMS providers and send bulk messages</p>
            </div>
        </div>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live="search" placeholder="Search provider or sender ID" />
                    </form>
                </div>
                <a href="#!" class="text-inherit">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </a>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('setup.smslogs') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-list"></i> SMS Logs
                </a>
                <button class="btn btn-success" wire:click="openBulkSmsModal">
                    <i class="fa-solid fa-paper-plane"></i> Send Bulk SMS
                </button>
                <x-forms.button-model name="ADD SETTING" />
            </div>
        </div>
        <div>
            <div class="card card-lg overflow-hidden" id="taskTable" data-list="name">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Provider Name</th>
                                    <th>Sender ID</th>
                                    <th>Sending URL</th>
                                    <th>Sender Name URL</th>
                                    <th>Default</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = ($smsApiSettings->currentPage() - 1) * $smsApiSettings->perPage() + 1;
                                @endphp
                                @forelse($smsApiSettings as $setting)
                                <tr>
                                    <td>{{ $number++ }}</td>
                                    <td class="name">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold">{{ $setting->provider_name }}</span>
                                            @if($setting->is_default)
                                                <span class="badge bg-primary-soft text-primary">Default</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $setting->sender_id ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-soft text-info" title="{{ $setting->sending_url }}">
                                            {{ Str::limit($setting->sending_url, 30) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($setting->sender_name_url)
                                            <span class="badge bg-secondary-soft text-secondary" title="{{ $setting->sender_name_url }}">
                                                {{ Str::limit($setting->sender_name_url, 30) }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($setting->is_default)
                                            <span class="badge bg-primary">
                                                <i class="fa-solid fa-check"></i> Yes
                                            </span>
                                        @else
                                            <button class="btn btn-sm btn-outline-primary" wire:click="setDefault('{{ $setting->id }}')"
                                                onclick="return confirm('Set this as default SMS API?')">
                                                Set as Default
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        @if($setting->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-warning" wire:click="openModal('edit', '{{ $setting->id }}')" title="Edit">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm {{ $setting->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                wire:click="toggleActive('{{ $setting->id }}')"
                                                title="{{ $setting->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fa-solid fa-{{ $setting->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" wire:click="delete('{{ $setting->id }}')"
                                                onclick="return confirm('Delete this SMS API setting?')" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
                                            <p>No SMS API settings found. Add one to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($smsApiSettings->hasPages())
                    <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                        <div class="d-flex gap-4">
                            <div>
                                <div class="pagination-buttons d-flex">
                                    {{ $smsApiSettings->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-pages.model :title="$modalMode === 'edit' ? 'Edit SMS API Setting' : 'Add SMS API Setting'"
                   :formaction="$modalMode === 'edit' ? 'update' : 'save'"
                   :modalMode="$modalMode"
                   :showModal="$showModal"
                   size="lg">
        <div class="row">
            <div class="col-md-6">
                <x-forms.input type="text" name="provider_name" label="Provider Name" placeholder="e.g., Twilio, Nexmo" required />
            </div>
            <div class="col-md-6">
                <x-forms.input type="text" name="sender_id" label="Sender ID" placeholder="e.g., CompanyName" />
            </div>
        </div>

        <x-forms.input type="text" name="sending_url" label="Sending URL" placeholder="https://api.provider.com/send" required />

        <x-forms.input type="text" name="delivery_report_url" label="Delivery Report URL" placeholder="https://api.provider.com/delivery-report" />

        <x-forms.input type="text" name="sender_name_url" label="Sender Name URL" placeholder="https://api.provider.com/sender-name" />

        <div class="row">
            <div class="col-md-6">
                <x-forms.input type="text" name="api_key" label="API Key" placeholder="Enter API Key" required />
            </div>
            <div class="col-md-6">
                <x-forms.input type="text" name="secret_key" label="Secret Key" placeholder="Enter Secret Key" required />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" wire:model="is_default" id="is_default" role="switch">
                        <label class="form-check-label" for="is_default">
                            <span class="fw-semibold">Set as Default</span>
                            <small class="d-block text-muted">This will be used as the primary SMS API</small>
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" wire:model="is_active" id="is_active" role="switch">
                        <label class="form-check-label" for="is_active">
                            <span class="fw-semibold">Active</span>
                            <small class="d-block text-muted">Enable or disable this API setting</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </x-pages.model>

    <!-- Bulk SMS Modal -->
    @if($showBulkSmsModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-paper-plane"></i> Send Bulk SMS
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeBulkSmsModal"></button>
                </div>
                <form wire:submit.prevent="sendBulkSms">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fa-solid fa-info-circle"></i>
                            <strong>Tips:</strong> Enter phone numbers separated by commas, spaces, or new lines.
                            Example: 0712345678, 0723456789 or one per line.
                        </div>

                        <div class="mb-3">
                            <label for="bulk_phone_numbers" class="form-label fw-semibold">
                                Phone Numbers <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control @error('bulk_phone_numbers') is-invalid @enderror"
                                id="bulk_phone_numbers"
                                wire:model="bulk_phone_numbers"
                                rows="5"
                                placeholder="Enter phone numbers (comma or newline separated)&#10;Example:&#10;0712345678&#10;0723456789&#10;0734567890"
                                required></textarea>
                            @error('bulk_phone_numbers')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bulk_message" class="form-label fw-semibold">
                                Message <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control @error('bulk_message') is-invalid @enderror"
                                id="bulk_message"
                                wire:model="bulk_message"
                                rows="4"
                                placeholder="Enter your SMS message here..."
                                maxlength="1000"
                                required></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                @error('bulk_message')
                                    <small class="text-danger">{{ $message }}</small>
                                @else
                                    <small class="text-muted">Maximum 1000 characters</small>
                                @enderror
                                <small class="text-muted">
                                    {{ strlen($bulk_message) }} / 1000
                                </small>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fa-solid fa-exclamation-triangle"></i>
                            <strong>Note:</strong> This will use your default SMS API provider.
                            Make sure you have sufficient SMS credits.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkSmsModal">
                            <i class="fa-solid fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-paper-plane"></i> Send SMS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
