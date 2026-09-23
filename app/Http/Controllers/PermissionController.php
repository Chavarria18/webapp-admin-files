<?php

namespace App\Http\Controllers;

use App\Enums\PermissionScope;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class PermissionController extends Controller
{
    /**
     * Matrix of actions (rows) by roles (columns).
     */
    public function index(): View
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $permissions = Permission::orderBy('id')->get()->groupBy('group');

        return view('permissions.index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'scopes' => PermissionScope::cases(),
        ]);
    }

    /**
     * Replace every role's grants with the submitted matrix.
     * grants[role_id][permission_id] = scope, or empty for "not allowed".
     */
    public function update(Request $request): RedirectResponse
    {
        $permissions = Permission::all()->keyBy('id');
        $roles = Role::all();

        $validated = $request->validate([
            'grants' => ['array'],
            'grants.*' => ['array'],
            'grants.*.*' => ['nullable', Rule::enum(PermissionScope::class)],
        ]);

        $grants = $validated['grants'] ?? [];

        validator($grants)->after(function (Validator $validator) use ($grants, $permissions) {
            foreach ($grants as $roleId => $row) {
                foreach ($row as $permissionId => $scope) {
                    $permission = $permissions->get($permissionId);

                    if (! $permission) {
                        $validator->errors()->add('grants', 'Permiso desconocido.');
                    } elseif ($scope && ! $permission->scoped && $scope !== PermissionScope::All->value) {
                        $validator->errors()->add('grants', "«{$permission->label}» no admite alcance, solo permitido o no.");
                    }
                }
            }
        })->validate();

        DB::transaction(function () use ($roles, $grants) {
            foreach ($roles as $role) {
                $role->permissions()->sync(
                    collect($grants[$role->id] ?? [])
                        ->filter()
                        ->map(fn (string $scope) => ['scope' => $scope])
                        ->all()
                );
            }
        });

        return redirect()->route('permissions.index')->with('success', 'Permisos actualizados.');
    }
}
