<x-app>
    <div class="container">
        <h1>Historial{{ $user ? " de {$user->name}" : '' }}</h1>

        @if ($user && $user->hasRole('admin'))
            <a href="{{ route('history') }}" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-left"></i> Ver historial completo
            </a>
        @endif

        <form action="{{ route('history') }}" method="GET" class="mb-3">
            @if ($user)
                <input type="hidden" name="user_id" value="{{ $user->id }}">
            @endif

            <div class="input-group">
                <input type="search" name="search" value="{{ $search }}" class="form-control"
                    placeholder="Buscar por nombre de archivo" aria-label="Buscar por nombre de archivo">
                <input type="date" name="date" value="{{ $date }}" class="form-control" style="max-width: 11rem;"
                    aria-label="Filtrar por fecha">
                <button type="submit" class="btn btn-outline-primary" title="Buscar">
                    <i class="bi bi-search"></i>
                </button>
                @if ($search !== '' || $date)
                    <a href="{{ route('history', $user ? ['user_id' => $user->id] : []) }}"
                        class="btn btn-outline-secondary" title="Limpiar búsqueda">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>

        @if ($historials->isEmpty())
            <p>{{ ($search !== '' || $date) ? 'No se encontraron movimientos.' : 'No hay movimientos registrados.' }}</p>
        @else
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Archivo</th>
                        <th>Fecha</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($historials as $historial)
                        @php
                            $actionIcon = match ($historial->action) {
                                'upload' => ['bi-upload', 'text-primary'],
                                'delete' => ['bi-trash', 'text-danger'],
                                'restore' => ['bi-arrow-counterclockwise', 'text-success'],
                                'force_delete' => ['bi-trash-fill', 'text-danger'],
                                default => ['bi-clock-history', 'text-secondary'],
                            };
                        @endphp
                        <tr>
                            <td>{{ $historial->username ?? $historial->user?->name ?? 'Usuario eliminado' }}</td>
                            <td>
                                <i class="bi {{ $actionIcon[0] }} {{ $actionIcon[1] }}"></i>
                                {{ ucfirst(str_replace('_', ' ', $historial->action)) }}
                            </td>
                            <td>{{ $historial->file_name ?? '—' }}</td>
                            <td>{{ $historial->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrapper mt-3">
                {{ $historials->links() }}
            </div>
        @endif
    </div>
</x-app>
