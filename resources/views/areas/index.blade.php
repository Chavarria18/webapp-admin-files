<x-app>
    <div class="container">
        <h1>Áreas</h1>

        <a href="{{ route('areas.create') }}" class="btn btn-primary mb-3">Nueva área</a>

        @if ($areas->isEmpty())
            <p>No hay áreas registradas.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuarios</th>
                        <th>Gerentes</th>
                        <th>Creada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($areas as $area)
                        <tr>
                            <td>{{ $area->name }}</td>
                            <td>{{ $area->usuarios_count }}</td>
                            <td>{{ $area->gerentes_count }}</td>
                            <td>{{ $area->created_at?->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('areas.edit', $area) }}" class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Editar área">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('areas.destroy', $area) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Eliminar área"
                                        onclick="return confirm('¿Está seguro de que desea eliminar esta área?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrapper mt-3">
                {{ $areas->links() }}
            </div>
        @endif
    </div>
</x-app>
