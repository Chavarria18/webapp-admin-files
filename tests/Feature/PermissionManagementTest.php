<?php

namespace Tests\Feature;

use App\Http\Middleware\CognitoAuth;
use App\Models\Area;
use App\Models\File;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    private Area $area;

    private Area $otherArea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(CognitoAuth::class);
        $this->withoutVite();

        $this->area = Area::create(['name' => 'Comercial']);
        $this->otherArea = Area::create(['name' => 'Operaciones']);
    }

    public function test_admin_sees_the_permission_matrix(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('permissions.index'))
            ->assertOk()
            ->assertSee('Ver y descargar archivos')
            ->assertSee('Áreas gestionadas');
    }

    public function test_non_admins_cannot_reach_the_permission_screen(): void
    {
        $gerente = $this->user('gerente');

        $this->actingAs($gerente)->get(route('permissions.index'))->assertForbidden();
        $this->actingAs($gerente)->put(route('permissions.update'), ['grants' => []])->assertForbidden();
    }

    public function test_granting_a_wider_scope_changes_what_the_role_can_see(): void
    {
        $estandar = $this->user('estandar');
        $colleague = $this->user('estandar');
        $file = File::create(['uuid' => 'u-1', 'name' => 'a.pdf', 's3dir' => 'x', 'size' => 1, 'user_id' => $colleague->id]);

        $this->assertFalse($estandar->can('view', $file));

        $this->saveMatrix(['estandar' => ['files.view' => 'area']]);

        $estandar = $estandar->fresh();
        $this->assertTrue($estandar->can('view', $file));
        $this->assertTrue(File::visibleTo($estandar)->whereKey($file->id)->exists());
    }

    public function test_revoking_a_permission_blocks_the_route(): void
    {
        $gerente = $this->user('gerente');

        $this->saveMatrix(['gerente' => ['users.view' => null]]);

        $this->actingAs($gerente->fresh())->get(route('users.index'))->assertForbidden();
    }

    public function test_unscoped_permissions_reject_a_scope(): void
    {
        $this->actingAs($this->user('admin'))
            ->put(route('permissions.update'), $this->matrixWith(['jefe_area' => ['users.create' => 'own']]))
            ->assertSessionHasErrors('grants');

        $this->assertNull(Role::firstWhere('name', 'jefe_area')->scopeFor('users.create'));
    }

    public function test_area_scope_limits_the_users_list(): void
    {
        $jefe = $this->user('jefe_area');
        $sameArea = $this->user('estandar');
        $otherArea = $this->user('estandar', $this->otherArea);

        $visible = User::visibleTo($jefe)->pluck('id');

        $this->assertTrue($visible->contains($sameArea->id));
        $this->assertFalse($visible->contains($otherArea->id));
    }

    public function test_delegated_user_permissions_cannot_touch_admins(): void
    {
        $this->saveMatrix(['gerente' => ['users.update' => 'all', 'users.delete' => 'all']]);

        $gerente = $this->user('gerente');
        $admin = $this->user('admin');
        $estandar = $this->user('estandar');

        $this->assertTrue($gerente->can('update', $estandar));
        $this->assertFalse($gerente->can('update', $admin));
        $this->assertFalse($gerente->can('delete', $admin));

        $this->actingAs($gerente)
            ->put(route('users.update', $estandar), ['name' => 'X', 'role' => 'admin', 'area_id' => $this->area->id])
            ->assertSessionHasErrors('role');

        $this->assertTrue($estandar->fresh()->hasRole('estandar'));
    }

    private function user(string $role, ?Area $area = null): User
    {
        return User::factory()->role($role)->create([
            'area_id' => $role === 'admin' || $role === 'gerente' ? null : ($area ?? $this->area)->id,
        ]);
    }

    /**
     * Submit the current matrix with some cells changed (null = not allowed).
     */
    private function saveMatrix(array $changes): void
    {
        $this->actingAs($this->user('admin'))
            ->put(route('permissions.update'), $this->matrixWith($changes))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('permissions.index'));
    }

    private function matrixWith(array $changes): array
    {
        $permissionIds = Permission::pluck('id', 'action');
        $grants = [];

        foreach (Role::with('permissions')->get() as $role) {
            foreach ($permissionIds as $action => $id) {
                $grants[$role->id][$id] = array_key_exists($action, $changes[$role->name] ?? [])
                    ? $changes[$role->name][$action]
                    : $role->scopeFor($action)?->value;
            }
        }

        return ['grants' => $grants];
    }
}
