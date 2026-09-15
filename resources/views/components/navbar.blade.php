<nav>
    <a href="{{ route('home') }}">Home</a>

    @if(auth()->user()->role === 'admin')
        <a >Users</a>
    @endif
</nav>