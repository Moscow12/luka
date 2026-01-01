<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10">
                {{-- Logo/Brand Section --}}
                <div class="text-center mb-4">
                    @if($workstation && $workstation->logo)
                        <img
                            src="{{ asset('storage/' . $workstation->logo) }}"
                            alt="{{ $workstation->workstation_name ?? $appName }}"
                            class="mb-3"
                            style="max-height: 80px; max-width: 200px; object-fit: contain;"
                        />
                        <h4 class="fw-semibold text-dark mb-0">{{ $workstation->workstation_name }}</h4>
                    @else
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-3" style="width: 70px; height: 70px;">
                            <span class="text-white fw-bold fs-3">{{ substr($appName, 0, 1) }}</span>
                        </div>
                        <h4 class="fw-semibold text-dark mb-0">{{ $appName }}</h4>
                    @endif
                </div>

                {{-- Login Card --}}
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4 p-md-5">
                        {{-- Welcome Text --}}
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark mb-2">Welcome Back</h2>
                            <p class="text-muted mb-0">Sign in to your account to continue</p>
                        </div>

                        {{-- Login Form --}}
                        <form wire:submit="loginUser">
                            {{-- Login Field --}}
                            <div class="mb-3">
                                <label for="login" class="form-label fw-medium text-dark">
                                    <i class="fa-solid fa-user me-1 text-muted"></i>
                                    Email, Username or Phone
                                </label>
                                <input
                                    type="text"
                                    id="login"
                                    wire:model="login"
                                    class="form-control form-control-lg border-2 @error('login') is-invalid @enderror"
                                    placeholder="Enter your email, username or phone"
                                    autocomplete="username"
                                />
                                @error('login')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Password Field --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label fw-medium text-dark mb-0">
                                        <i class="fa-solid fa-lock me-1 text-muted"></i>
                                        Password
                                    </label>
                                    <a href="{{ route('forgot-password') }}" class="text-primary text-decoration-none small fw-medium">
                                        Forgot Password?
                                    </a>
                                </div>
                                <input
                                    type="password"
                                    id="password"
                                    wire:model="password"
                                    class="form-control form-control-lg border-2 mt-2 @error('password') is-invalid @enderror"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                />
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Remember Me --}}
                            <div class="mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="rememberMe">
                                    <label class="form-check-label text-muted" for="rememberMe">
                                        Keep me signed in
                                    </label>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="d-grid">
                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg fw-semibold py-3"
                                    wire:loading.attr="disabled"
                                >
                                    <span wire:loading.remove wire:target="loginUser">
                                        <i class="fa-solid fa-right-to-bracket me-2"></i>
                                        Sign In
                                    </span>
                                    <span wire:loading wire:target="loginUser">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Signing In...
                                    </span>
                                </button>
                            </div>
                        </form>

                        @if($twoFactorEnabled)
                            {{-- Divider --}}
                            <div class="d-flex align-items-center my-4">
                                <hr class="flex-grow-1">
                                <span class="px-3 text-muted small">or continue with</span>
                                <hr class="flex-grow-1">
                            </div>

                            {{-- Social Login Buttons --}}
                            <div class="d-flex gap-3">
                                <a href="#" class="btn btn-outline-secondary flex-fill py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                                        <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                                    </svg>
                                    Google
                                </a>
                                <a href="#" class="btn btn-outline-secondary flex-fill py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                                        <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                                    </svg>
                                    Facebook
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Security Notice --}}
                <div class="text-center mt-4">
                    <p class="text-muted small mb-0">
                        <i class="fa-solid fa-shield-halved me-1"></i>
                        Your connection is secure and encrypted
                    </p>
                </div>

                {{-- Footer --}}
                @if($workstation)
                    <div class="text-center mt-3">
                        <p class="text-muted small mb-0">
                            {{ $workstation->workstation_name ?? $appName }}
                            @if($workstation->physical_address)
                                <br><span class="text-muted">{{ $workstation->physical_address }}</span>
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
