<x-app>
    <form method="POST" action="{{ $action }}">
        @csrf

        @if($type === 'register')
            <input type="text" name="name" placeholder="Name" required>
            <select name="rol" required>
                <option value="">Selecciona un rol</option>
                <option value="estandar">Usuario estándar</option>
                <option value="jefe_area">Jefe de área</option>
                <option value="gerente">Gerente</option>
                <option value="admin">Administrador</option>
            </select>
            <select name="area_id" required>
                <option value="">Selecciona un área</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                @endforeach
            </select>
        @endif

        @if($type != 'new-password')
            <input type="email" name="email" placeholder="Email" required>
        @endif

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">
            {{ $type === 'register' ? 'Register' : 'Login' }}
        </button>
    </form>
</x-app>