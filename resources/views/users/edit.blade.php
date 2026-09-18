<x-app>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <div class="container">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}" required>

                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>


            <div class="mb-3">
                <label for="role" class="form-label">Rol</label>

                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="estandar" {{ old('role', $user->role) === 'estandar' ? 'selected' : '' }}>
                        Estándar
                    </option>

                    <option value="jefe_area" {{ old('role', $user->role) === 'jefe_area' ? 'selected' : '' }}>
                        Jefe de Área
                    </option>

                    <option value="gerente" {{ old('role', $user->role) === 'gerente' ? 'selected' : '' }}>
                        Gerente
                    </option>

                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>
                </select>

                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($user->role === 'gerente')

                <div class="mb-3">
                    <label for="area_ids" class="form-label">Áreas</label>

                    <select name="area_ids[]" id="area_ids" class="form-select @error('area_ids') is-invalid @enderror"
                        multiple required>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" @selected($user->areasGestionadas->contains('id', $area->id))>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('area_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @error('area_ids.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            @else

                <div class="mb-3">
                    <label for="area_id" class="form-label">Área</label>

                    <select name="area_id" id="area_id" class="form-select @error('area_id') is-invalid @enderror" required>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" @selected($user->area_id == $area->id)>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('area_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            @endif

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

</x-app>

<script>
    $(document).ready(function () {
        $('#area_ids').select2({
            placeholder: 'Selecciona áreas',
            width: '100%'
        });
    });
</script>