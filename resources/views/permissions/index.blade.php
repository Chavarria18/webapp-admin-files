<x-app>
    <div class="container">
        <h1>Permisos</h1>

        <p class="text-muted">
            Define qué puede hacer cada rol. En las acciones con alcance, elige sobre qué registros aplica:
            <strong>Propios</strong> (solo los suyos), <strong>Su área</strong>,
            <strong>Áreas gestionadas</strong> (las que tiene asignadas un gerente) o <strong>Todos</strong>.
        </p>

        <form action="{{ route('permissions.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Acción</th>
                            @foreach ($roles as $role)
                                <th>{{ $role->label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $group => $groupPermissions)
                            <tr class="table-light">
                                <th colspan="{{ $roles->count() + 1 }}">{{ $group }}</th>
                            </tr>

                            @foreach ($groupPermissions as $permission)
                                <tr>
                                    <td>
                                        {{ $permission->label }}
                                        <div class="small text-muted">{{ $permission->action }}</div>
                                    </td>

                                    @foreach ($roles as $role)
                                        @php
                                            $current = old(
                                                "grants.{$role->id}.{$permission->id}",
                                                $role->permissions->firstWhere('id', $permission->id)?->pivot->scope ?? ''
                                            );
                                        @endphp
                                        <td>
                                            <select name="grants[{{ $role->id }}][{{ $permission->id }}]"
                                                class="form-select form-select-sm"
                                                aria-label="{{ $permission->label }} — {{ $role->label }}">
                                                <option value="">No permitido</option>

                                                @if ($permission->scoped)
                                                    @foreach ($scopes as $scope)
                                                        <option value="{{ $scope->value }}" @selected($current === $scope->value)>
                                                            {{ $scope->label() }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option value="all" @selected($current === 'all')>Permitido</option>
                                                @endif
                                            </select>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    Guardar permisos
                </button>
            </div>
        </form>
    </div>
</x-app>
