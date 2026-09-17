<x-app>

    <div class="container register-page">
        <div class="row">            
            <div class="col-md-6 register-container">

                @include('auth.form', [
                    'type' => 'register',
                    'action' => route('users.register')
                ])
            </div>
        </div>
    </div>


</x-app>