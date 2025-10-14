<!DOCTYPE html>
<html lang="en" data-bs-theme="auto" class="collapsed">
  @include('components.layouts.partials.header')
  <body>
    <!-- Vertical Sidebar -->
    <div>
      <div id="miniSidebar" >
        @include('components.layouts.partials.logo')

        @include('components.layouts.partials.navbar-vertical')
      </div>
      
      <!-- Offcanvas Sidebar -->
      <div class="offcanvasNav offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">

            <a class='d-flex align-items-center gap-2' href="{{ route('dashboard') }}">
              <img src="{{ asset('images/brand/logo/logo-icon.svg') }}" alt="" />
              <span class="fw-bold fs-4  site-logo-text">HRP</span>
            </a>

          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
          @include('components.layouts.partials.navbar-vertical')
        </div>
      </div>

      <!-- Main Content -->
      <div id="content" class="position-relative h-100">
        <!-- navbar -->
        @include('components.layouts.partials.navbar-top')
        <!--Offcanvas notification-->


        <!-- container -->
        <div class="custom-container">
          
          {{ $slot }}

        </div>
      </div>
    </div>
    <!-- Libs JS -->
    {!! ToastMagic::scripts() !!}
  </body>

</html>
