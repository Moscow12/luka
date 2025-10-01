<ul class="navbar-nav flex-column  ">
    <!-- Nav item -->
    <li class="nav-item">
      <a class='nav-link' href='index.html'><span class="nav-icon"><svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-files"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 3v4a1 1 0 0 0 1 1h4" /><path d="M18 17h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h4l5 5v7a2 2 0 0 1 -2 2z" /><path d="M16 17v2a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h2" /></svg> <span class="text">Project</span></a
      >
    </li>

    <!-- Nav item -->
    <li class="nav-item">
      <div class="nav-heading">Apps</div>
      <hr class="mx-5 nav-line mb-1" />
    </li>
    <!-- Nav item -->
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#e-mail" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="nav-icon">
          <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-mail"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
        </span>
        <span class="text">Email</span>
      </a>
      <ul class="dropdown-menu flex-column">
        <li class="nav-item">
          <a class='nav-link' href='apps/email/mail.html'>Inbox</a>
        </li>
        <li class="nav-item">
          <a class='nav-link' href='apps/email/mail-details.html'>Email Detail</a>
        </li>
        <li class="nav-item">
          <a class='nav-link' href='apps/email/compose.html'>Compose</a>
        </li>
      </ul>
    </li>


   <!-- Nav item -->
    <li class="nav-item">
      <a class='nav-link' href='docs/index.html'>
        <span class="nav-icon">
          <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1.5"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-code"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 13l-1 2l1 2" /><path d="M14 13l1 2l-1 2" /></svg>
        </span>
        <span class="text">Docs</span>
      </a>
    </li>
    
    <!-- Nav item -->
    <li>
      <div class="text-center py-5 upgrade-ui ">
        <div>
          <img src="{{ asset('images/avatar/avatar-1.jpg') }} " alt="" class="avatar avatar-md rounded-circle">
          <div class="my-3">
                  <h5 class="mb-1 fs-6">Jitu Chauhan</h5>
            <span class="text-secondary">Free Version - 1 Month</span>
          </div>
          <a href="#!" class="btn btn-primary">Upgrade</a>

        </div>

      </div>
    </li>

  </ul>