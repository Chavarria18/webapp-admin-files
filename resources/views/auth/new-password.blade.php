<x-app>

@include('auth.form', [
    'type' => 'new-password',
    'action' => route('auth.new-password.store')
])


</x-app>