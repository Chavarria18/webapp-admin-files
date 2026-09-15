<x-app>

    @include('auth.form', [
        'type' => 'login',
        'action' => route('auth.login.register')
    ])

</x-app>