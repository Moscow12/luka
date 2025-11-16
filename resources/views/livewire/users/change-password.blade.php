<div>
    <div class="custom-container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page header -->
                <div class="mb-5">
                    <h1 class="mb-2 h2"><i class="fas fa-key text-primary me-2"></i>Change Password</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('user.profile') }}">Profile</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session()->has('success'))
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
                            <div>
                                <strong>Success!</strong><br>
                                {{ session('success') }}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Password Requirements Info Card -->
            <div class="col-xl-4 col-lg-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-light border-0">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle text-info me-2"></i>Password Requirements
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 small">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                At least 8 characters long
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Contains uppercase and lowercase letters
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Contains at least one number
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Contains at least one special character (@$!%*?&)
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Must be different from your current password
                            </li>
                        </ul>
                    </div>

                    <!-- Security Tips Section -->
                    <div class="card-header bg-light border-0 border-top">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb text-warning me-2"></i>Security Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 small">
                            <li class="mb-2">
                                <strong>Use a unique password:</strong> Don't reuse passwords from other accounts
                            </li>
                            <li class="mb-2">
                                <strong>Make it memorable:</strong> Use a passphrase or combination of random words
                            </li>
                            <li class="mb-2">
                                <strong>Change regularly:</strong> Update your password every 3-6 months
                            </li>
                            <li class="mb-2">
                                <strong>Enable 2FA:</strong> Consider enabling two-factor authentication for extra security
                            </li>
                            <li class="mb-0">
                                <strong>Keep it private:</strong> Never share your password with anyone
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Change Password Form Card -->
            <div class="col-xl-8 col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>Update Your Password
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form wire:submit.prevent="updatePassword">
                            <!-- Current Password -->
                            <div class="mb-4">
                                <label for="current_password" class="form-label fw-semibold">
                                    Current Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input
                                        type="{{ $showCurrentPassword ? 'text' : 'password' }}"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        id="current_password"
                                        wire:model.live="current_password"
                                        placeholder="Enter your current password"
                                        autocomplete="current-password">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        wire:click="toggleCurrentPassword">
                                        <i class="fas fa-eye{{ $showCurrentPassword ? '-slash' : '' }}"></i>
                                    </button>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>Enter your existing password to confirm your identity
                                </small>
                            </div>

                            <hr class="my-4">

                            <!-- New Password -->
                            <div class="mb-4">
                                <label for="new_password" class="form-label fw-semibold">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input
                                        type="{{ $showNewPassword ? 'text' : 'password' }}"
                                        class="form-control @error('new_password') is-invalid @enderror"
                                        id="new_password"
                                        wire:model.live="new_password"
                                        placeholder="Enter your new password"
                                        autocomplete="new-password">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        wire:click="toggleNewPassword">
                                        <i class="fas fa-eye{{ $showNewPassword ? '-slash' : '' }}"></i>
                                    </button>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mt-2">
                                    @php
                                        $passwordLength = strlen($new_password ?? '');
                                        $strengthClass = $passwordLength < 8 ? 'bg-danger' : ($passwordLength < 12 ? 'bg-warning' : 'bg-success');
                                        $strengthLabel = $passwordLength < 8 ? 'Weak' : ($passwordLength < 12 ? 'Medium' : 'Strong');
                                        $strengthColor = $passwordLength < 8 ? 'text-danger' : ($passwordLength < 12 ? 'text-warning' : 'text-success');
                                        $strengthWidth = min(100, ($passwordLength / 12) * 100);
                                    @endphp
                                    <div class="progress progress-strength">
                                        <div class="progress-bar {{ $strengthClass }}"
                                            role="progressbar"
                                            aria-valuenow="{{ $strengthWidth }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                            data-width="{{ $strengthWidth }}">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Password strength:
                                        <span class="{{ $strengthColor }} fw-semibold">{{ $strengthLabel }}</span>
                                    </small>
                                </div>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-4">
                                <label for="new_password_confirmation" class="form-label fw-semibold">
                                    Confirm New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <input
                                        type="{{ $showConfirmPassword ? 'text' : 'password' }}"
                                        class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                        id="new_password_confirmation"
                                        wire:model.live="new_password_confirmation"
                                        placeholder="Re-enter your new password"
                                        autocomplete="new-password">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        wire:click="toggleConfirmPassword">
                                        <i class="fas fa-eye{{ $showConfirmPassword ? '-slash' : '' }}"></i>
                                    </button>
                                    @error('new_password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($new_password && $new_password_confirmation && $new_password === $new_password_confirmation)
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>Passwords match
                                    </small>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                                <a href="{{ route('user.profile') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        <i class="fas fa-save me-2"></i>Update Password
                                    </span>
                                    <span wire:loading>
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Updating...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .input-group-text {
            min-width: 45px;
            justify-content: center;
        }

        .progress-strength {
            height: 5px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .card {
            border-radius: 10px;
            overflow: hidden;
        }

        .card-header {
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        }

        .alert {
            border-radius: 10px;
        }

        @media (max-width: 1199.98px) {
            /* Stack cards on smaller screens */
            .col-xl-4, .col-xl-8 {
                margin-bottom: 1rem;
            }
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', ({ el, component }) => {
                const progressBar = el.querySelector('.progress-bar[data-width]');
                if (progressBar) {
                    const width = progressBar.getAttribute('data-width');
                    progressBar.style.width = width + '%';
                }
            });
        });
    </script>
</div>
