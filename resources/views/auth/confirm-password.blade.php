<x-app>

    <div class="container login-page">
        <div class="row">

            <div class="col-md-6 login-container">

                <div class="login-form-wrapper">
                    <div class="login-logo">
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo">
                    </div>
                    <h1 class="login-title">Nueva contraseña</h1>
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

                    <form method="POST" action="{{ route('auth.confirm-forgot-password') }}" class="login-form">
                        @csrf

                        <input type="text" name="code" placeholder="Código de verificación" class="form-control"
                            required>

                        <input type="password" name="password" placeholder="Nueva contraseña" class="form-control"
                            required>

                        <input type="password" name="password_confirmation" placeholder="Confirmar nueva contraseña"
                            class="form-control" required>
                        <div class="form-group">
                            <a href="{{ route('auth.login') }}">
                                Volver al inicio de sesión
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Restablecer contraseña
                        </button>
                    </form>




                </div>

            </div>
            <div class="col-md-6 newpss-image">
            </div>
        </div>
    </div>



</x-app>