
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

    @include('components.layouts.partials.header')

    <body>
        <main class="d-flex flex-column justify-content-center vh-100">

            {{ $slot }}

            <x-ui.theme-switcher iconLibrary="bi" buttonClass="btn-light" :withWrapper="true" />

        </main>

        <!-- Libs JS -->
        {!! ToastMagic::scripts() !!}
    </body>

</html>
