<x-app>

    <div class="container login-page">
        <div class="row">

            <div class="col-md-6 login-container">

                <div class="login-form-wrapper">
                    <div class="login-logo">
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo">
                    </div>
                    <h1 class="login-title">Forgot password</h1>
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

                        <input type="email" name="email" placeholder="Email" class="form-control" required>
                        <div class="form-group">
                            <a href="{{ route('auth.login') }}">
                                Back to login
                            </a>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Send reset code
                        </button>
                    </form>



                </div>

            </div>
            <div class="col-md-6 forgot-image">
            </div>
        </div>
    </div>



</x-app>