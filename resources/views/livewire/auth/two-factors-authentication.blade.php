<div>
    <section>
        <div class="container">
            <div class="row mb-8">
                <div class="col-xl-4 offset-xl-4 col-md-12 col-12">
                    <div class="text-center">
                        <a class="fs-2 fw-bold d-flex align-items-center gap-2 justify-content-center mb-6" href="{{ route('login') }}">
                            <img src="{{ asset('images/brand/logo/logo-icon.svg') }}" alt="">
                            <span>Dasher</span>
                        </a>
                        <h1 class="mb-1">OTP Verification</h1>
                        <p class="mb-0">
                            We sent a code to
                            <a href="#" class="text-inherit">{{ $maskedEmail }}</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-8 col-md-10 col-12">
                    <div class="card card-lg mb-6">
                        <div class="card-body p-6">
                            <form wire:submit="verifyTwoFactor"
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
                                            // Move to next input
                                            if (index < 5) {
                                                const inputs = event.target.parentElement.querySelectorAll('input');
                                                inputs[index + 1].focus();
                                            }
                                        } else if (value.length === 0) {
                                            this.digits[index] = '';
                                            this.updateToken();
                                        } else {
                                            // Reject non-numeric input
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
                                <div class="d-flex flex-row gap-2 mb-3">
                                    <template x-for="(digit, index) in digits" :key="index">
                                        <input
                                            type="text"
                                            class="form-control inputpass-code"
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
                                    <div class="text-danger text-center mb-3">
                                        <small>{{ $message }}</small>
                                    </div>
                                @enderror

                                <div class="row">
                                    <div class="mb-3 col-md-12">
                                        <x-forms.checkbox
                                            wire:model="remember_device"
                                            name="rememberDeviceCheckbox"
                                            label="Trust this device for 30 days"
                                        />

                                        <x-forms.button
                                            type="submit"
                                            :block="true"
                                            loadingText="Verifying..."
                                            loadingTarget="verifyTwoFactor"
                                            class="mb-3"
                                        >
                                            Verify
                                        </x-forms.button>

                                        <div class="text-center mb-3">
                                            <x-forms.button
                                                wire:click="resendCode"
                                                variant="link"
                                                loadingText="Sending..."
                                                loadingTarget="resendCode"
                                                class="p-0"
                                            >
                                                Didn't receive the code? Resend
                                            </x-forms.button>
                                        </div>

                                        <div class="text-center">
                                            <a href="{{ route('login') }}">
                                                <span>Back to Login</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>