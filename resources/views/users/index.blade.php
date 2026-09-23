<x-app>
    <div class="container">
        <h1>Usuarios</h1>
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('users.organigrama') }}" class="btn btn-info mb-3">
                Organigrama
            </a>
            <a href="{{ route('users.register') }}" class="btn btn-primary mb-3">Nuevo usuario</a>
        @endif
        <form method="GET" action="{{ route('users.index') }}" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por correo..."
                    value="{{ request('search') }}">

                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i>
                </button>

                @if(request('search'))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-danger">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>

        @if ($users->isEmpty())
            <p>No hay usuarios registrados.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo electrónico</th>
                        <th>Rol</th>
                        <th>Área</th>
                        <th>Archivos</th>
                        <th>Creado</th>
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
                            <th>Historial</th>
                        @endif
                        @if(auth()->user()->role === 'admin')
                            <th>Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{  strtoupper(str_replace("_"," ",$user->role)) }}</td>
                            <td>
                                @if ($user->role === 'gerente')
                                    {{ $user->areasGestionadas->pluck('name')->join(', ') ?: '—' }}
                                @else
                                    {{ $user->area?->name ?? '—' }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('home', ['user_id' => $user->id]) }}" class="btn btn-outline-secondary btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Ver archivos">
                                    <i class="bi bi-folder"></i> {{ $user->files_count }}
                                </a>
                            </td>
                            <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                           
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
                                <td>
                                    <a href="{{ route('history', ['user_id' => $user->id]) }}"
                                        class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Ver historial">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                </td>
                            @endif
                             @if(auth()->user()->role === 'admin')
                                <td>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Editar usuario">
                                        <i class="bi bi-pencil"></i>
                                    </a>


                                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Eliminar usuario"
                                            onclick="return confirm('¿Está seguro de que desea eliminar este usuario? Se transferiran los archivos del usuario eliminado a este usuario actual')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>


                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrapper mt-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-app>