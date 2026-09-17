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
                <input type="text" name="search" class="form-control" placeholder="Search by name..."
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
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Área</th>
                        <th>Files</th>
                        <th>Creado</th>
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
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->area?->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('home', ['user_id' => $user->id]) }}"
                                    class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="View files">
                                    <i class="bi bi-folder"></i>
                                </a>
                            </td>
                            <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                            @if(auth()->user()->role === 'admin')
                                <td>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Download file">
                                        <i class="bi bi-pencil"></i>
                                    </a>


                                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Delete file"
                                            onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                    <a href="{{ route('history', ['user_id' => $user->id]) }}"
                                        class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="View history">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
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