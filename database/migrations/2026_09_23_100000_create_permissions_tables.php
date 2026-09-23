<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Actions the application checks: action => [group, label, scoped].
     * Scoped actions apply to records owned by someone (own / area / managed / all).
     */
    private const PERMISSIONS = [
        'files.create' => ['Archivos', 'Subir archivos', false],
        'files.view' => ['Archivos', 'Ver y descargar archivos', true],
        'files.update' => ['Archivos', 'Editar archivos', true],
        'files.delete' => ['Archivos', 'Enviar archivos a la papelera', true],
        'files.restore' => ['Archivos', 'Restaurar archivos de la papelera', true],
        'files.force_delete' => ['Archivos', 'Eliminar archivos definitivamente', true],
        'users.view' => ['Usuarios', 'Ver usuarios', true],
        'users.create' => ['Usuarios', 'Crear usuarios', false],
        'users.update' => ['Usuarios', 'Editar usuarios', true],
        'users.delete' => ['Usuarios', 'Eliminar usuarios', true],
        'users.organigram' => ['Usuarios', 'Ver organigrama', false],
        'history.view' => ['Historial', 'Ver historial', true],
        'areas.manage' => ['Áreas', 'Gestionar áreas', false],
    ];

    /**
     * Initial grants, matching the rules that were hard-coded in the policies.
     * role => [action => scope]
     */
    private const GRANTS = [
        'admin' => '*',
        'gerente' => [
            'files.create' => 'all',
            'files.view' => 'managed',
            'files.update' => 'managed',
            'files.delete' => 'managed',
            'files.restore' => 'managed',
            'files.force_delete' => 'managed',
            'users.view' => 'managed',
            'history.view' => 'managed',
        ],
        'jefe_area' => [
            'files.create' => 'all',
            'files.view' => 'area',
            'files.update' => 'area',
            'files.delete' => 'own',
            'files.restore' => 'own',
            'files.force_delete' => 'own',
            'users.view' => 'area',
        ],
        'estandar' => [
            'files.create' => 'all',
            'files.view' => 'own',
            'files.update' => 'own',
            'files.delete' => 'own',
            'files.restore' => 'own',
            'files.force_delete' => 'own',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('action')->unique();
            $table->string('label');
            $table->string('group');
            $table->boolean('scoped')->default(false);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->string('scope')->default('all');
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });

        $now = now();

        foreach (self::PERMISSIONS as $action => [$group, $label, $scoped]) {
            DB::table('permissions')->insert([
                'action' => $action, 'label' => $label, 'group' => $group, 'scoped' => $scoped,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $permissionIds = DB::table('permissions')->pluck('id', 'action');
        $roleIds = DB::table('roles')->pluck('id', 'name');

        foreach (self::GRANTS as $role => $grants) {
            if (! isset($roleIds[$role])) {
                continue;
            }

            $grants = $grants === '*' ? array_fill_keys(array_keys(self::PERMISSIONS), 'all') : $grants;

            foreach ($grants as $action => $scope) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleIds[$role], 'permission_id' => $permissionIds[$action], 'scope' => $scope,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
    }
};
