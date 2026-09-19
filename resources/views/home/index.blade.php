<x-app>


    <div class="container">
        <h1>{{ $filterUser ? "Archivos de {$filterUser->name}" : 'Mis archivos' }}</h1>

        @if ($filterUser)
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-left"></i> Ver todos los archivos
            </a>
        @endif

        <div class="row">
            <div class="col-md-8">
                <form action="{{ route('home') }}" method="GET" class="mb-3">
                    @if ($filterUser)
                        <input type="hidden" name="user_id" value="{{ $filterUser->id }}">
                    @endif

                    <div class="input-group">
                        <input type="search" name="search" value="{{ $search }}" class="form-control"
                            placeholder="Buscar por identificador único o nombre" aria-label="Buscar archivos">
                        <button type="submit" class="btn btn-outline-primary" title="Buscar">
                            <i class="bi bi-search"></i>
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('home', $filterUser ? ['user_id' => $filterUser->id] : []) }}"
                                class="btn btn-outline-secondary" title="Limpiar búsqueda">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </form>

                @if ($files->isEmpty())
                    <p>No se encontraron archivos.</p>
                @else
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Identificador único</th>
                                <th>Nombre original</th>
                                <th>Tamaño</th>
                                <th>Subido</th>
                                @if(auth()->user()->role !== 'estandar')
                                    <th>Subido por</th>
                                @endif
                                @if(auth()->user()->role === 'gerente')
                                    <th>Área</th>
                                @endif
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($files as $file)
                                <tr @class(['table-success' => session('new_file_id') === $file->id])>
                                    <td>
                                        <button type="button" class="btn btn-outline-secondary btn-sm js-copy-uuid"
                                            data-uuid="{{ $file->uuid }}" title="Copiar identificador único"
                                            aria-label="Copiar identificador único">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </td>
                                    <td>{{ $file->email }}</td>

                                    <td>
                                        {{ number_format($file->size / 1024, 2) }} KB
                                    </td>

                                    <td>
                                        {{ $file->created_at->format('Y-m-d H:i') }}
                                    </td>

                                    @if(auth()->user()->role !== 'estandar')
                                        <td>{{ $file->user->name }}</td>
                                    @endif

                                    @if(auth()->user()->role === 'gerente')
                                        <td>
                                            {{ $file->user->area?->name ?? 'Gerente' }}
                                        </td>
                                    @endif

                                    <td>
                                        <a href="{{ route('files.download', $file) }}" class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Descargar archivo">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form action="{{ route('files.destroy', $file) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Eliminar archivo"
                                                onclick="return confirm('¿Está seguro de que desea eliminar este archivo?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                    <div class="pagination-wrapper mt-3">
                        {{ $files->links() }}
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                @include('files.info', ['metrics' => $metrics])
                <a href="{{ route('files') }}" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Subir archivo">
                    <i class="bi bi-upload"></i>
                </a>

                <a href="{{ route('recycle') }}" class="btn btn-info" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Papelera de reciclaje">
                    <i class="bi bi-trash"></i>
                </a>
            </div>

        </div>

    </div>

    <script>
        document.querySelectorAll('.js-copy-uuid').forEach(function (button) {
            button.addEventListener('click', function () {
                navigator.clipboard.writeText(button.dataset.uuid).then(function () {
                    const icon = button.querySelector('i');
                    icon.className = 'bi bi-check-lg';
                    button.classList.replace('btn-outline-secondary', 'btn-outline-success');

                    setTimeout(function () {
                        icon.className = 'bi bi-copy';
                        button.classList.replace('btn-outline-success', 'btn-outline-secondary');
                    }, 1500);
                });
            });
        });
    </script>

</x-app>