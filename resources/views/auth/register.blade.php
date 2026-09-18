<x-app>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="container register-page">
        <div class="row">
            <div class="col-md-6 register-container">

                @include('auth.form', [
                    'type' => 'register',
                    'action' => route('users.register.store')
                ])
            </div>
        </div>
    </div>


</x-app>

<script>
    $(document).ready(function () {
        $('#area_ids').select2({
            placeholder: 'Selecciona áreas',
            width: '100%'
        });

        function updateAreaMode() {
            const isGerente = $('#rol').val() === 'gerente';

            $('#area-id-group').toggleClass('d-none', isGerente);
            $('#area-ids-group').toggleClass('d-none', !isGerente);

            $('#area_id').prop('required', !isGerente).prop('disabled', isGerente);
            $('#area_ids').prop('required', isGerente).prop('disabled', !isGerente);
        }

        $('#rol').on('change', updateAreaMode);
        updateAreaMode();
    });
</script>