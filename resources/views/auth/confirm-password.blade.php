<x-app>

    <div class="container login-page">
        <div class="row">

            <div class="col-md-6 login-container">

                <div class="login-form-wrapper">
                    <div class="login-logo">
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo">
                    </div>
                    <h1 class="login-title">New Password</h1>
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

                        <input type="text" name="code" placeholder="Verification code" class="form-control" required>

                        <input type="password" name="password" placeholder="New password" class="form-control" required>

                        <input type="password" name="password_confirmation" placeholder="Confirm new password"
                            class="form-control" required>

                        <button type="submit" class="btn btn-primary">
                            Reset password
                        </button>
                    </form>




                </div>

            </div>
            <div class="col-md-6 newpss-image">
            </div>
        </div>
    </div>



</x-app>