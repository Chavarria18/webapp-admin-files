<div class="files-info">

    {{-- Row 1 --}}
    <div class="row g-3 mb-3">

        {{-- Total files --}}
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted d-block">
                                Total de archivos
                            </small>

                            <h3 class="mb-0">
                                {{ number_format($metrics['total_files']) }}
                            </h3>
                        </div>

                        <div class="fs-2">
                            📁
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Created today --}}
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted d-block">
                                Creados hoy
                            </small>

                            <h3 class="mb-0">
                                {{ number_format($metrics['files_today']) }}
                            </h3>
                        </div>

                        <div class="fs-2">
                            📄
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>


    {{-- Row 2 --}}
    <div class="row g-3">

        {{-- Created this month --}}
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted d-block">
                                Este mes
                            </small>

                            <h3 class="mb-0">
                                {{ number_format($metrics['files_this_month']) }}
                            </h3>
                        </div>

                        <div class="fs-2">
                            📊
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Storage --}}
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted d-block">
                               Almacenamiento usado
                            </small>

                            <h3 class="mb-0">
                                {{ round($metrics['total_size'] / 1024 / 1024, 4) }} MB
                            </h3>
                        </div>

                        <div class="fs-2">
                            💾
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
