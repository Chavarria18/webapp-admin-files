<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administrador de Archivos</title>

    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @unless(request()->routeIs('auth.*'))
        @include('components.navbar')
    @endunless

    @unless(request()->routeIs('auth.*'))
        <div class="container">
            <div class="row">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        {{ session('success') }}

                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                            &times;
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        {{ $errors->first() }}

                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                            &times;
                        </button>
                    </div>
                @endif

            </div>

            <div class="row">
                <div class="sheet">
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    @else

        <main class="auth-layout">
            {{ $slot }}
        </main>

    @endunless
    <footer class="footer">

        <div class="container">

            <div class="footer-content">

                <div class="footer-brand">
                    <i class="bi bi-folder-fill"></i>
                    <span>Administrador de Archivos</span>
                </div>

                <div class="footer-info">
                    <span>
                        <i class="bi bi-shield-check"></i>
                        Aplicación web de administración de archivos
                    </span>

                    <span>
                        © {{ date('Y') }} Administrador de Archivos
                    </span>
                </div>

            </div>

        </div>

    </footer>
</body>

</html>