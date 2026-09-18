<x-app>


    <div class="container">
        <h1>Papelera de reciclaje</h1>

        <div class="row">
            <div class="col-md-8">
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
                                <th>Actualizado</th>
                                @if(auth()->user()->role === 'gerente')
                                    <a>Área</a>
                                @endif
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($files as $file)
                                <tr>
                                    <td>{{ $file->uuid }}</td>
                                    <td>{{ $file->name }}</td>

                                    <td>
                                        {{ number_format($file->size / 1024, 2) }} KB
                                    </td>

                                    <td>
                                        {{ $file->created_at->format('Y-m-d H:i') }}
                                    </td>

                                    <td>
                                        {{ $file->updated_at->format('Y-m-d H:i') }}
                                    </td>
                                    @if(auth()->user()->role === 'gerente')
                                        <td>
                                            {{ $file->user->area->name }}
                                        </td>
                                    @endif

                                    <td>
                                        <a href="{{ route('files.download', $file) }}" class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Descargar archivo">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form action="{{ route('files.fdestroy', $file) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Eliminar archivo"
                                                onclick="return confirm('¿Está seguro de que desea eliminar permanentemente este archivo?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('files.restore', $file->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn btn-outline-success btn-sm"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Restaurar archivo">
                                                <i class="bi bi-arrow-counterclockwise"></i>
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

        </div>

    </div>


</x-app>