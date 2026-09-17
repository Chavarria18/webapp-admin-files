<x-app>
    <div class="container">
        <h1>Usuarios</h1>

        <a href="{{ route('users.register') }}" class="btn btn-primary mb-3">Nuevo usuario</a>

        @if ($users->isEmpty())
            <p>No hay usuarios registrados.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Área</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->area?->name ?? '—' }}</td>
                            <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('users.edit', $user) }}">Editar</a>

                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('¿Eliminar este usuario?')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-app>