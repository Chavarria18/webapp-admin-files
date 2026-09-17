<div class="container">
    <div class="row">
        <div class="col-md-6">
            <form id="uploadForm"
                action="{{ isset($archivo) ? route('archivos.update', $archivo) : route('files.store') }}" method="POST"
                enctype="multipart/form-data">

                @csrf

                @if(isset($archivo))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <input type="file" name="file" id="file" class="form-control">

                    @if(isset($archivo))
                        <small class="text-muted">
                            Deja vacío para conservar el archivo actual.
                        </small>
                    @endif

                    @error('file')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    {{ isset($archivo) ? 'Actualizar' : 'Subir' }}
                </button>
            </form>
        </div>



        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center">

                    <h5 class="card-title mb-4">Vista previa</h5>


                    <div class="mb-3">
                        <img id="filePreview" src="{{ asset('images/file-placeholder.jpg') }}" alt="Vista previa" class="img-fluid"
                            style="max-height: 220px; max-width: 100%; object-fit: contain;">
                    </div>


                    <div id="fileInfo" class="text-start">

                        <div class="mb-2">
                            <strong>Nombre:</strong>
                            <span id="fileName">
                                {{ isset($archivo) ? $archivo->nombre : 'Ningún archivo seleccionado' }}
                            </span>
                        </div>

                        <div class="mb-2">
                            <strong>Tamaño:</strong>
                            <span id="fileSize">
                                {{ isset($archivo) ? number_format($archivo->size / 1024, 2) . ' KB' : '-' }}
                            </span>
                        </div>

                        <div class="mb-2">
                            <strong>Tipo:</strong>
                            <span id="fileType">
                                {{ isset($archivo) ? $archivo->mime_type : '-' }}
                            </span>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>


<script>
    document.getElementById('file').addEventListener('change', function (event) {

        const file = event.target.files[0];

        if (!file) {
            return;
        }

        const preview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileType = document.getElementById('fileType');


        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileType.textContent = file.type || 'Desconocido';

        if (file.type.startsWith('image/')) {

            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
            };

            reader.readAsDataURL(file);

        } else {


            preview.src = "{{ asset('images/file-document.png') }}";
        }
    });


    function formatFileSize(bytes) {

        if (bytes === 0) {
            return '0 Bytes';
        }

        const units = ['Bytes', 'KB', 'MB', 'GB'];

        const i = Math.floor(
            Math.log(bytes) / Math.log(1024)
        );

        return (
            parseFloat(
                (bytes / Math.pow(1024, i)).toFixed(2)
            )
            + ' '
            + units[i]
        );
    }

    document.getElementById('uploadForm').addEventListener('submit', async function (event) {
        event.preventDefault();

        const fileInput = document.getElementById('file');
        const file = fileInput.files[0];

        if (!file) {
            return;
        }

        const response = await fetch(
            `{{ route('files.check-name') }}?name=${encodeURIComponent(file.name)}`
        );

        const data = await response.json();

        if (data.exists) {
            const uploadAnyway = confirm(
                `${file.name} already exists. Upload anyway?`
            );

            if (!uploadAnyway) {
                return;
            }
        }
        this.submit();
    });
</script>