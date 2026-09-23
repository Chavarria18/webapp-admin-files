<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="{{ route('home') }}">Inicio</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup"
        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            @if(! auth()->user()->hasRole('estandar'))
                <a href="{{ route('users.index') }}" class="nav-link">
                    Usuarios
                </a>
            @endif
            @if(auth()->user()->hasRole('admin'))


                <a href="{{ route('history') }}" class="nav-link">
                    Historial
                </a>

                <a href="{{ route('areas.index') }}" class="nav-link">
                    Áreas
                </a>

            @endif
            <div class="navbar-right">
                @php
                    $roleLabel = mb_strtoupper(auth()->user()->role->label);
                    $areasGestionadas = auth()->user()->hasRole('gerente') && !auth()->user()->area
                        ? auth()->user()->areasGestionadas->pluck('name')
                        : collect();
                @endphp
                <span class="role">
                    {{ auth()->user()->name }} -
                    @if(auth()->user()->area)
                        {{ auth()->user()->area->name }} -
                        {{ $roleLabel }}
                    @elseif(auth()->user()->hasRole('gerente'))
                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn btn-link dropdown-toggle text-reset text-decoration-none p-0 fw-medium align-baseline"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                {{ $roleLabel }} · {{ $areasGestionadas->count() }}
                                {{ $areasGestionadas->count() === 1 ? 'ÁREA' : 'ÁREAS' }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end areas-dropdown">
                                @forelse($areasGestionadas as $areaName)
                                    <li><span class="dropdown-item-text">{{ $areaName }}</span></li>
                                @empty
                                    <li><span class="dropdown-item-text text-muted">Sin áreas asignadas</span></li>
                                @endforelse
                            </ul>
                        </div>
                    @else
                        {{ $roleLabel }}
                    @endif
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