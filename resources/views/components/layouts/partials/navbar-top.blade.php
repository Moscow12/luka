<div class="navbar-glass navbar navbar-expand-lg px-0 px-lg-4">
  <div class="container-fluid px-lg-0">
    <div class="d-flex align-items-center gap-4">
      <!-- Collapse -->
      <div class="d-block d-lg-none">
        <a class="text-inherit" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="icon icon-tabler icons-tabler-outline icon-tabler-menu-2"
          >
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 6l16 0" />
            <path d="M4 12l16 0" />
            <path d="M4 18l16 0" />
          </svg>
        </a>
      </div>
      
      <x-ui.sidebar-toggle />

    </div>

    <!-- Navbar nav -->
    <ul class="list-unstyled d-flex align-items-center mb-0 gap-2">
      <!-- Light dark mode-->
      <li>
        <x-ui.theme-switcher iconLibrary="ti" buttonClass="btn-ghost" :withWrapper="false" />
      </li>
      <!-- Notifications -->
      <li>
        @livewire('common.notifications')
      </li>
      <!-- Dropdown -->
      <li class="ms-3 dropdown">
        @php
            $user = auth()->user();
            $userPhoto = $user->profile_picture;
            $userName = $user->full_name ?: ($user->first_name ?? $user->email);
            $userInitial = strtoupper(substr($user->first_name ?? $user->email, 0, 1));
        @endphp
        <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          @if($userPhoto)
            <img src="{{ asset('storage/' . $userPhoto) }}" alt="{{ $userName }}" class="avatar avatar-sm rounded-circle" style="object-fit: cover;" />
          @else
            <div class="avatar avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
              {{ $userInitial }}
            </div>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-0">
          <div>
            <div class="d-flex gap-3 align-items-center border-dashed border-bottom px-4 py-4">
              @if($userPhoto)
                <img src="{{ asset('storage/' . $userPhoto) }}" alt="{{ $userName }}" class="avatar avatar-md rounded-circle" style="object-fit: cover;" />
              @else
                <div class="avatar avatar-md rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-4">
                  {{ $userInitial }}
                </div>
              @endif
              <div>
                <h4 class="mb-0 fs-5">{{ $userName }}</h4>
                <p class="mb-0 text-secondary small">{{ $user->email }}</p>
              </div>
            </div>
            <div class="p-3 d-flex flex-column gap-1">
              <a href="{{ route('dashboard') }}" class="dropdown-item d-flex align-items-center gap-2">
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-home-2">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                    <path d="M10 12h4v4h-4z" />
                  </svg>
                </span>
                <span>Home</span>
              </a>
              <a href="{{ route('user.profile') }}" class="dropdown-item d-flex align-items-center gap-2">
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                  </svg>
                </span>
                <span>Account Settings</span>
              </a>
              <a href="{{ route('user.change-password') }}" class="dropdown-item d-flex align-items-center gap-2">
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-key">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239 .578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0z" />
                    <path d="M15 9h.01" />
                  </svg>
                </span>
                <span>Change Password</span>
              </a>
            </div>

            <livewire:auth.logout />
          </div>
        </div>
      </li>
    </ul>
  </div>
</div>

<!-- Notification Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNotification" aria-labelledby="offcanvasNotificationLabel">
    <div class="sticky-top bg-white">
        <div class="offcanvas-header gap-4">
            <div class="d-flex justify-content-between w-100">
                <h5 class="mb-0" id="offcanvasNotificationLabel">Notifications</h5>
                <div class="d-flex gap-3 align-items-center">
                    <a href="#" class="link-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Refresh" onclick="Livewire.dispatch('refreshNotifications')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-refresh">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                        </svg>
                    </a>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>

    <div class="offcanvas-body p-0">
        <div data-simplebar="" style="height: 800px">
            <div class="list-group list-group-flush">
                @livewire('common.notifications-list')
            </div>
        </div>
    </div>
</div>
