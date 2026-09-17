<form method="POST" action="{{ $action }}"  class="{{ $type === 'login' ? 'login-form' : 'register-form' }}">
    @csrf

    @if($type === 'register')
        <div class="form-group">
            <input
                type="text"
                name="name"
                placeholder="Name"
                value="{{ old('name') }}"
                required
                class="form-control"
            >
        </div>

        <div class="form-group">
            <select name="rol" required class="form-control">
                <option value="">Selecciona un rol</option>
                <option value="estandar" {{ old('rol') === 'estandar' ? 'selected' : '' }}>Usuario estándar</option>
                <option value="jefe_area" {{ old('rol') === 'jefe_area' ? 'selected' : '' }}>Jefe de área</option>
                <option value="gerente" {{ old('rol') === 'gerente' ? 'selected' : '' }}>Gerente</option>
                <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>Administrador</option>
            </select>
        </div>

        <div class="form-group">
            <select name="area_id" required class="form-control">
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
    @endif

    @if($type != 'new-password')
        <div class="form-group">
            <input
                type="email"
                name="email"
                placeholder="Email"
                value="{{ old('email') }}"
                required
                class="form-control"
            >
        </div>
    @endif

    <div class="form-group">
        <input
            type="password"
            name="password"
            placeholder="Password"
            required
            class="form-control"
        >
    </div>

    @if($type === 'login')
        <div class="form-group">
            <a href="{{ route('auth.forgot-password') }}">
                Forgot password?
            </a>
        </div>
    @endif

    <button type="submit" class="btn btn-primary">
        {{ $type === 'register' ? 'Register' : 'Login' }}
    </button>
</form>