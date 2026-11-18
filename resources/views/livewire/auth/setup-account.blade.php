<div class="min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden"
     style="background: linear-gradient(180deg, #87CEEB 0%, #B0E0E6 30%, #E0F4FF 60%, #FFFFFF 100%);">

    {{-- Logo in top left --}}
    <div class="position-absolute top-0 start-0 p-4">
        <div class="d-flex align-items-center gap-2">
            @if($workstation && $workstation->logo)
                <img src="{{ asset('storage/' . $workstation->logo) }}" alt="{{ $workstation->workstation_name ?? $appName }}"
                     style="max-height: 40px; object-fit: contain;" />
            @else
                <div class="d-inline-flex align-items-center justify-content-center bg-dark rounded-2" style="width: 32px; height: 32px;">
                    <span class="text-white fw-bold small">{{ substr($appName, 0, 1) }}</span>
                </div>
            @endif
            <span class="fw-semibold text-dark">{{ $workstation->workstation_name ?? $appName }}</span>
        </div>
    </div>

    <div class="container position-relative" style="z-index: 10;">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10">
                <div class="card border-0 shadow-lg rounded-4 mx-auto" style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.95); max-width: 480px;">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-3 mb-3" style="width: 72px; height: 72px;">
                                <i class="fa-solid fa-user-shield fa-xl text-dark"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Set Up Your Account</h4>
                            @if ($linkValid)
                                <p class="text-muted mb-0 small">
                                    Choose a password for<br>
                                    <span class="fw-medium text-dark">{{ $username }}</span>
                                </p>
                            @endif
                        </div>

                        @if ($linkValid)
                            <form wire:submit="setPassword" autocomplete="off">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-medium small text-muted mb-1">New password</label>
                                    <input type="password" id="password" wire:model="password" autocomplete="new-password"
                                           class="form-control @error('password') is-invalid @enderror" placeholder="At least 8 characters" />
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label fw-medium small text-muted mb-1">Confirm password</label>
                                    <input type="password" id="password_confirmation" wire:model="password_confirmation" autocomplete="new-password"
                                           class="form-control" placeholder="Repeat the password" />
                                </div>

                                <button type="submit" class="btn btn-dark w-100" wire:loading.attr="disabled" wire:target="setPassword">
                                    <span wire:loading.remove wire:target="setPassword">Set password</span>
                                    <span wire:loading wire:target="setPassword">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...
                                    </span>
                                </button>

                                <p class="text-muted small text-center mt-3 mb-0">This link works once and expires {{ $expireHours }} hours after it was sent.</p>
                            </form>
                        @else
                            <div class="alert alert-warning small" role="alert">
                                @error('password') {{ $message }} @else This setup link is invalid, expired or has already been used. @enderror
                            </div>
                            <p class="small text-muted mb-3">
                                Ask HR to send you a new setup link, or use "Forgot password" with the e-mail address on your staff record.
                            </p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('forgot-password') }}" class="btn btn-outline-dark w-50">Forgot password</a>
                                <a href="{{ route('login') }}" class="btn btn-dark w-50">Sign in</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
