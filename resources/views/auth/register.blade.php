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
            // Not "required": Select2 hides the real <select>, so the browser could not show
            // its own pop-up. Validated manually on submit instead.
            $('#area_ids').prop('disabled', !isGerente);
            $('#area-ids-error').addClass('d-none');
        }

        function areasMissing() {
            return $('#rol').val() === 'gerente' && !($('#area_ids').val() || []).length;
        }

        $('#rol').on('change', updateAreaMode);
        updateAreaMode();

        $('#area_ids').on('change', function () {
            if (!areasMissing()) {
                $('#area-ids-error').addClass('d-none');
            }
        });

        $('.register-form').on('submit', function (event) {
            if (areasMissing()) {
                event.preventDefault();
                $('#area-ids-error').removeClass('d-none').css('display', 'block');
                $('#area_ids').select2('open');
            }
        });
    });
</script>