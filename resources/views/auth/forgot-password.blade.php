<x-app>

    <div class="container login-page">
        <div class="row">

            <div class="col-md-6 login-container">

                <div class="login-form-wrapper">
                    <div class="login-logo">
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo">
                    </div>
                    <h1 class="login-title">Recuperar contraseña</h1>
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

                    <form method="POST" action="{{ route('auth.send-reset-code') }}" class="login-form">
                        @csrf

                        <input type="email" name="email" placeholder="Correo electrónico" class="form-control" required>
                        <div class="form-group">
                            <a href="{{ route('auth.login') }}">
                                Volver al inicio de sesión
                            </a>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Enviar código de restablecimiento
                        </button>
                    </form>



                </div>

            </div>
            <div class="col-md-6 forgot-image">
            </div>
        </div>
    </div>



</x-app>