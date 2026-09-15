<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>File Admin</title>

    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @unless(request()->routeIs('auth.*'))
        @include('components.navbar')
    @endunless


    @if (session('success'))
        <div class="parchment-flash parchment-flash--success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="parchment-flash parchment-flash--error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="parchment-flash parchment-flash--error">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="sheet">




        <main>
            {{ $slot }}
        </main>





    </div>



</body>

</html>