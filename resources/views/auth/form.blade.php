<form method="POST" action="{{ $action }}"  class="{{ $type === 'login' ? 'login-form' : 'register-form' }}">
    @csrf

    @if($type === 'register')
        <div class="form-group">
            <input
                type="text"
                name="name"
                placeholder="Nombre"
                value="{{ old('name') }}"
                required
                class="form-control"
            >
        </div>

        <div class="form-group">
            <select name="rol" id="rol" required class="form-control">
                <option value="">Selecciona un rol</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ old('rol') === $role->name ? 'selected' : '' }}>{{ $role->label }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="area-id-group">
            <select name="area_id" id="area_id" class="form-control">
                <option value="">Selecciona un área</option>

                @foreach($areas as $area)
                    <option
                        value="{{ $area->id }}"
                        {{ old('area_id') == $area->id ? 'selected' : '' }}
                    >
                        {{ $area->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group d-none" id="area-ids-group">
            <select name="area_ids[]" id="area_ids" class="form-control" multiple>
                @foreach($areas as $area)
                    <option
                        value="{{ $area->id }}"
                        {{ collect(old('area_ids'))->contains($area->id) ? 'selected' : '' }}
                    >
                        {{ $area->name }}
                    </option>
                @endforeach
            </select>
            <div id="area-ids-error" class="invalid-feedback d-none">
                Selecciona al menos un área.
            </div>
        </div>
    @endif

    @if($type != 'new-password')
        <div class="form-group">
            <input
                type="email"
                name="email"
                placeholder="Correo electrónico"
                value="{{ old('email') }}"
                required
                class="form-control"
            >
        </div>
    @endif

    <div class="form-group">
        <div class="input-group">
            <input
                type="password"
                name="password"
                id="password"
                placeholder="Contraseña"
                required
                class="form-control"
            >
            <button
                type="button"
                id="toggle-password"
                class="btn btn-outline-secondary btn-sm"
            >
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    @if($type === 'login')
        <div class="form-group">
            <a href="{{ route('auth.forgot-password') }}">
                ¿Olvidaste tu contraseña?
            </a>
        </div>
    @endif

    <button type="submit" class="btn btn-primary">
            @if($type == 'new-password')
            Actualizar
            @endif
            @if($type != 'new-password')
                {{ $type === 'register' ? 'Registrar usuario' : 'Iniciar sesión' }}
            @endif
        
    </button>
</form>

<script>
    document.getElementById('toggle-password').addEventListener('click', function () {
        const password = document.getElementById('password');
        const icon = this.querySelector('i');
        const isHidden = password.type === 'password';

        password.type = isHidden ? 'text' : 'password';
        icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>