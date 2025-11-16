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
      <!-- Pages link -->
      <li>
        <button type="button" class="btn btn-white" data-bs-toggle="modal" data-bs-target="#searchModal">
          <span
            ><svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="icon icon-tabler icons-tabler-outline icon-tabler-search"
            >
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <circle cx="10" cy="10" r="7" />
              <line x1="21" y1="21" x2="15" y2="15" />
            </svg>
          </span>
          <small class="ms-1">⌘K</small>
        </button>
        <!-- Modal -->
      </li>
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
        <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="{{ asset('images/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-sm rounded-circle" />
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-0">
          <div>
            <div class="d-flex gap-3 align-items-center border-dashed border-bottom px-4 py-4">
              <img src="{{ asset('images/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-md rounded-circle" />
              <div>
                <h4 class="mb-0 fs-5">Jitu Chauhan</h4>
                <p class="mb-0 text-secondar small">@imjituchauhan</p>
              </div>
            </div>
            <div class="p-3 d-flex flex-column gap-1">
              <a href="{{ route('dashboard') }}" class="dropdown-item d-flex align-items-center gap-2">
                <span
                  ><svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-home-2"
                  >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                    <path d="M10 12h4v4h-4z" />
                  </svg>
                </span>
                <span>Home</span>
              </a>
              <a href="#!" class="dropdown-item d-flex align-items-center gap-2">
                <span
                  ><svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-inbox"
                  >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                    <path d="M4 13h3l3 3h4l3 -3h3" />
                  </svg>
                </span>
                <span> Inbox</span>
              </a>
              <a href="#!" class="dropdown-item d-flex align-items-center gap-2">
                <span
                  ><svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-message"
                  >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M8 9h8" />
                    <path d="M8 13h6" />
                    <path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" />
                  </svg>
                </span>
                <span> Chat</span>
              </a>
              <a href="#!" class="dropdown-item d-flex align-items-center gap-2">
                <span
                  ><svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-activity"
                  >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M3 12h4l3 8l4 -16l3 8h4" />
                  </svg>
                </span>
                <span> Activity</span>
              </a>
              <a href="{{ route('user.profile') }}" class="dropdown-item d-flex align-items-center gap-2">
                <span
                  ><svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-settings"
                  >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                      d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"
                    />
                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                  </svg>
                </span>
                <span> Account Settings</span>
              </a>
              <a href="{{ route('user.change-password') }}" class="dropdown-item d-flex align-items-center gap-2">
                <span
                  ><svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-key"
                  >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239 .578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0z" />
                    <path d="M15 9h.01" />
                  </svg>
                </span>
                <span> Change Password</span>
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
                <h5 class="mb-0" id="offcanvasNotificationLabel">Pending Approvals</h5>
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

<!-- Modal of pages -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
        <input type="search" class="form-control border-0 rounded-0 ps-0 form-focus-none" id="globalSearchInput" placeholder="Search any word..." aria-label="Search" aria-describedby="search-addon" />
        <button type="button" class="btn btn-white btn-sm" data-bs-dismiss="modal" aria-label="Close">Esc</button>
        </div>
        <div class="modal-body" data-simplebar="" style="height: 400px">
        <div class="mb-4">
            <div class="d-flex flex-column border-bottom border-dashed py-4">
            <div class="mb-2">
                <span class="fw-semibold text-secondary small">Dashboard</span>
            </div>
            <div>
                <ul class="list-unstyled lh-lg mb-0">
                <li><a class='text-inherit' href='dashboard/analytics.html'>Analytics</a></li>
                <li><a href="dashboard/project.html" class="text-inherit">Project</a></li>
                <li><a class='text-inherit' href='dashboard/ecommerce.html'>Ecommerce</a></li>
                <li><a class='text-inherit' href='dashboard/crm.html'>CRM</a></li>
                <li><a class='text-inherit' href='dashboard/finance.html'>Finance</a></li>
                <li><a class='text-inherit' href='dashboard/blog.html'>Blog</a></li>
                </ul>
            </div>
            </div>
            <div class="d-flex flex-column border-bottom border-dashed py-4">
            <div class="mb-2">
                <span class="fw-semibold text-secondary small">Apps</span>
            </div>
            <div>
                <ul class="list-unstyled lh-lg mb-0">
                <li><a class='text-inherit' href='apps/calendar.html'> Calendar</a></li>
                <li><a class='text-inherit' href='apps/chat-app.html'> Chat</a></li>
                <li><a class='text-inherit' href='apps/email/mail.html'>Email</a></li>
                <li><a class='text-inherit' href='apps/e-commerce/ecommerce-products.html'>Ecommerce</a></li>
                <li><a class='text-inherit' href='apps/kanban.html'> Kanban</a></li>
                <li><a class='text-inherit' href='apps/project/project-grid.html'>Project</a></li>
                <li><a class='text-inherit' href='dashboard/file.html'> File Manager</a></li>
                <li><a class='text-inherit' href='apps/crm/crm-contacts.html'> CRM</a></li>
                <li><a class='text-inherit' href='apps/invoice/invoice-list.html'> Invoice</a></li>
                <li><a class='text-inherit' href='apps/profile/profile-overview.html'> Profile</a></li>
                <li><a class='text-inherit' href='apps/blog/blog-list.html'> Blog</a></li>
                </ul>
            </div>
            </div>
            <div class="d-flex flex-column border-bottom border-dashed py-4">
            <div>
                <span class="fw-semibold text-secondary small">Pages</span>
            </div>
            </div>
            <div class="d-flex flex-column border-bottom border-dashed py-4">
            <div>
                <span class="fw-semibold text-secondary small">Quick Links</span>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>
</div>
