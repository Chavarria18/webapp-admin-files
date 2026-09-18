<x-app>
    <div class="container">
        <h1>Áreas</h1>

        <a href="{{ route('areas.create') }}" class="btn btn-primary mb-3">Crear área</a>

        @if ($areas->isEmpty())
            <p>No hay áreas registradas</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Estándar</th>
                        <th>Jefes de área</th>
                        <th>Gerentes</th>
                        <th>Creada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($areas as $area)
                        <tr>
                            <td>{{ $area->name }}</td>
                            <td>
                                <a href="{{ route('users.index', ['area_id' => $area->id, 'role' => 'estandar']) }}">
                                    {{ $area->estandar_count }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('users.index', ['area_id' => $area->id, 'role' => 'jefe_area']) }}">
                                    {{ $area->jefes_area_count }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('users.index', ['area_id' => $area->id, 'role' => 'gerente']) }}">
                                    {{ $area->gerentes_count }}
                                </a>
                            </td>
                            <td>{{ $area->created_at?->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('areas.edit', $area) }}" class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Editar">
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
