<x-app>

    @include('auth.form', [
        'type' => 'login',
        'action' => route('auth.login.store')
    ])

</x-app>