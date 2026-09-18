
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

                    @include('auth.form', [
                        'type' => 'new-password',
                        'action' => route('auth.new-password.store')
                    ])




                </div>

            </div>
            <div class="col-md-6 newpss-image">
            </div>
        </div>
    </div>



</x-app>