<x-app>

    <h1>Forgot Password</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('auth.send-reset-code') }}">
        @csrf

        <input
            type="email"
            name="email"
            placeholder="Email"
            required
        >

        <button type="submit">
            Send reset code
        </button>
    </form>

</x-app>