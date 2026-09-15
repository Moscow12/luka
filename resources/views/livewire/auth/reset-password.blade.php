<div class="min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden"
     style="background: linear-gradient(180deg, #87CEEB 0%, #B0E0E6 30%, #E0F4FF 60%, #FFFFFF 100%);">

    {{-- Decorative Cloud Elements --}}
    <div class="position-absolute w-100 h-100" style="pointer-events: none; overflow: hidden;">
        {{-- Large arc decoration --}}
        <div class="position-absolute" style="
            width: 800px;
            height: 800px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        "></div>
        <div class="position-absolute" style="
            width: 600px;
            height: 600px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        "></div>

        {{-- Cloud shapes at bottom --}}
        <div class="position-absolute" style="
            bottom: -50px;
            left: -100px;
            width: 300px;
            height: 150px;
            background: rgba(255,255,255,0.6);
            border-radius: 100px;
            filter: blur(30px);
        "></div>
        <div class="position-absolute" style="
            bottom: -30px;
            right: -50px;
            width: 250px;
            height: 120px;
            background: rgba(255,255,255,0.5);
            border-radius: 100px;
            filter: blur(25px);
        "></div>
        <div class="position-absolute" style="
            bottom: 0;
            left: 30%;
            width: 400px;
            height: 100px;
            background: rgba(255,255,255,0.4);
            border-radius: 100px;
            filter: blur(20px);
        "></div>
    </div>

    {{-- Logo in top left --}}
    <div class="position-absolute top-0 start-0 p-4">
        @if($workstation && $workstation->logo)
            <div class="d-flex align-items-center gap-2">
                <img
                    src="{{ asset('storage/' . $workstation->logo) }}"
                    alt="{{ $workstation->workstation_name ?? $appName }}"
                    style="max-height: 40px; object-fit: contain;"
                />
                <span class="fw-semibold text-dark">{{ $workstation->workstation_name ?? $appName }}</span>
            </div>
        @else
            <div class="d-flex align-items-center gap-2">
                <div class="d-inline-flex align-items-center justify-content-center bg-dark rounded-2" style="width: 32px; height: 32px;">
                    <span class="text-white fw-bold small">{{ substr($appName, 0, 1) }}</span>
                </div>
                <span class="fw-semibold text-dark">{{ $appName }}</span>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <div class="container position-relative" style="z-index: 10;">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10">

                {{-- Reset Card --}}
                <div class="card border-0 shadow-lg rounded-4 mx-auto" style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.95); max-width: 480px;">
                    <div class="card-body p-4 p-md-5">

                        {{-- Logo & Title --}}
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-3 mb-3 overflow-hidden" style="width: 72px; height: 72px;">
                                @if($workstation && $workstation->logo)
                                    <img
                                        src="{{ asset('storage/' . $workstation->logo) }}"
                                        alt="{{ $workstation->workstation_name ?? $appName }}"
                                        style="max-width: 100%; max-height: 100%; object-fit: contain;"
                                    />
                                @else
                                    <i class="fa-solid fa-key fa-xl text-dark"></i>
                                @endif
                            </div>
                            @if($workstation && $workstation->workstation_name)
                                <div class="fw-semibold text-dark mb-2">{{ $workstation->workstation_name }}</div>
                            @endif
                            @if ($showTokenForm)
                                <h4 class="fw-bold text-dark mb-2">Reset Code Verification</h4>
                                <p class="text-muted mb-0 small">
                                    We sent a code to<br>
                                    <span class="fw-medium text-dark">{{ $email }}</span>
                                </p>
                            @else
                                <h4 class="fw-bold text-dark mb-2">Set New Password</h4>
                                <p class="text-muted mb-0 small">
                                    Enter your new password below
                                </p>
                            @endif
                        </div>

                        @if ($showTokenForm)
                            {{-- Token Verification Form --}}
                            <form wire:submit="resetPassword"
                                x-data="{
                                    digits: ['', '', '', '', '', ''],
                                    updateToken() {
                                        $wire.token = this.digits.join('');
                                    },
                                    handleInput(index, event) {
                                        const value = event.target.value;
                                        if (value.length === 1 && /^[0-9]$/.test(value)) {
                                            this.digits[index] = value;
                                            this.updateToken();
                                            if (index < 5) {
                                                const inputs = event.target.parentElement.querySelectorAll('input');
                                                inputs[index + 1].focus();
                                            }
                                        } else if (value.length === 0) {
                                            this.digits[index] = '';
                                            this.updateToken();
                                        } else {
                                            event.target.value = '';
                                        }
                                    },
                                    handleKeydown(index, event) {
                                        if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
                                            event.preventDefault();
                                            const inputs = event.target.parentElement.querySelectorAll('input');
                                            inputs[index - 1].focus();
                                        }
                                    },
                                    handlePaste(event) {
                                        event.preventDefault();
                                        const pasteData = event.clipboardData.getData('text').trim();
                                        if (/^\d{6}$/.test(pasteData)) {
                                            const pasteDigits = pasteData.split('');
                                            pasteDigits.forEach((digit, i) => {
                                                this.digits[i] = digit;
                                            });
                                            this.updateToken();
                                            const inputs = event.target.parentElement.querySelectorAll('input');
                                            inputs[5].focus();
                                        }
                                    }
                                }"
                                @paste="handlePaste($event)">
                                <div class="d-flex flex-row gap-2 mb-3 justify-content-center">
                                    <template x-for="(digit, index) in digits" :key="index">
                                        <input
                                            type="text"
                                            class="form-control text-center fw-bold fs-5 rounded-3"
                                            style="width: 48px; height: 56px;"
                                            maxlength="1"
                                            x-model="digits[index]"
                                            @input="handleInput(index, $event)"
                                            @keydown="handleKeydown(index, $event)"
                                            inputmode="numeric"
                                            pattern="[0-9]"
                                        >
                                    </template>
                                </div>

                                @error('token')
                                    <div class="text-danger small text-center mb-3">{{ $message }}</div>
                                @enderror

                                <div class="d-grid mb-3">
                                    <button
                                        type="submit"
                                        class="btn btn-dark btn-lg fw-semibold py-3 rounded-3"
                                        wire:loading.attr="disabled"
                                    >
                                        <span wire:loading.remove wire:target="resetPassword">
                                            Verify Code
                                        </span>
                                        <span wire:loading wire:target="resetPassword">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Verifying...
                                        </span>
                                    </button>
                                </div>

                                <div class="text-center mt-4 small">
                                    <a href="{{ route('forgot-password') }}" class="text-primary text-decoration-none fw-medium">Request New Code</a>
                                    <span class="mx-2 text-muted">|</span>
                                    <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-medium">Back to Login</a>
                                </div>
                            </form>
                        @else
                            {{-- New Password Form --}}
                            <form wire:submit="resetPassword">
                                {{-- New Password --}}
                                <div class="mb-3" x-data="{ show: false }">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fa-solid fa-lock text-muted"></i>
                                        </span>
                                        <input
                                            :type="show ? 'text' : 'password'"
                                            id="password"
                                            wire:model="password"
                                            class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror"
                                            placeholder="New Password"
                                            autocomplete="new-password"
                                        />
                                        <button
                                            type="button"
                                            class="input-group-text bg-light border-start-0"
                                            @click="show = !show"
                                            :aria-label="show ? 'Hide password' : 'Show password'"
                                            tabindex="-1"
                                        >
                                            <i class="fa-solid text-muted" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div class="mb-3" x-data="{ show: false }">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fa-solid fa-lock text-muted"></i>
                                        </span>
                                        <input
                                            :type="show ? 'text' : 'password'"
                                            id="password_confirmation"
                                            wire:model="password_confirmation"
                                            class="form-control border-start-0 border-end-0 ps-0"
                                            placeholder="Confirm Password"
                                            autocomplete="new-password"
                                        />
                                        <button
                                            type="button"
                                            class="input-group-text bg-light border-start-0"
                                            @click="show = !show"
                                            :aria-label="show ? 'Hide password' : 'Show password'"
                                            tabindex="-1"
                                        >
                                            <i class="fa-solid text-muted" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid mb-3">
                                    <button
                                        type="submit"
                                        class="btn btn-dark btn-lg fw-semibold py-3 rounded-3"
                                        wire:loading.attr="disabled"
                                    >
                                        <span wire:loading.remove wire:target="resetPassword">
                                            Reset Password
                                        </span>
                                        <span wire:loading wire:target="resetPassword">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Resetting Password...
                                        </span>
                                    </button>
                                </div>

                                <div class="text-center mt-4 small">
                                    <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-medium">Back to Login</a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Security Notice --}}
                <div class="text-center mt-4">
                    <p class="text-dark small mb-0">
                        <i class="fa-solid fa-shield-halved me-1"></i>
                        Your connection is secure and encrypted
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
