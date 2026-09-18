<x-app>
    <div class="container">
        <h1>Historial{{ $user ? " de {$user->name}" : '' }}</h1>

        @if ($user && $user->role == "admin")
            <a href="{{ route('history') }}" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-left"></i> Ver historial completo
            </a>
        @endif

        @if ($historials->isEmpty())
            <p>No hay movimientos registrados.</p>
        @else
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Fecha</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($historials as $historial)
                        <tr>
                            <td>{{ $historial->username ?? $historial->user?->name ?? 'Usuario eliminado' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $historial->action)) }}</td>
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
