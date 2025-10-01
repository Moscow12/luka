
<!DOCTYPE html>
<html lang="en">

    @include('components.partials.header')

    <body>
        <main class="d-flex flex-column justify-content-center vh-100">
        <!--Sign up start-->
        {{ $slot }}
        <!--Sign up end-->
        @include('components.partials.theme-toggle')
        </main>

        <!-- Libs JS -->
        {!! ToastMagic::scripts() !!}
    </body>

</html>
