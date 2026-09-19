<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="{{ route('home') }}">Inicio</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup"
        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            @if(auth()->user()->role !== 'estandar')
                <a href="{{ route('users.index') }}" class="nav-link">
                    Usuarios
                </a>
            @endif
            @if(auth()->user()->role === 'admin')


                <a href="{{ route('history') }}" class="nav-link">
                    Historial
                </a>

                <a href="{{ route('areas.index') }}" class="nav-link">
                    Áreas
                </a>

            @endif
            <div class="navbar-right">
                <span class="role">
                    @if(auth()->user()->area)
                       {{ auth()->user()->name }} -  {{ auth()->user()->area->name  }} -
                    @endif
                    {{ strtoupper(str_replace("_"," ",auth()->user()->role)) }}
                </span>

                <form method="GET" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="nav-link btn btn-link">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>