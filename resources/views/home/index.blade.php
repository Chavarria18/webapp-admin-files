<x-app>


    <div class="container">
        <h1>My Files</h1>

        <div class="row">
            <div class="col-md-8">
                @if ($files->isEmpty())
                    <p>No files found.</p>
                @else
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Unique Identifier</th>
                                <th>Original Name</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th>Updated</th>
                                @if(auth()->user()->role === 'gerente')
                                    <a>Area</a>
                                @endif
                                <th>Action</th>
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
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Download file">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form action="{{ route('files.destroy', $file) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Delete file"
                                                onclick="return confirm('Are you sure you want to delete this file?')">
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
                    title="Upload file">
                    <i class="bi bi-upload"></i>
                </a>

                <a href="{{ route('recycle') }}" class="btn btn-info" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Recycle bin">
                    <i class="bi bi-trash"></i>
                </a>
            </div>

        </div>

    </div>


</x-app>