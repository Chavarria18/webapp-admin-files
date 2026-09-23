<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'admin:admin,gerente'])->get('/_test/managers', fn () => 'ok');
        Route::middleware(['web', 'admin'])->get('/_test/admins', fn () => 'ok');
    }

    public function test_migration_seeds_the_reference_roles(): void
    {
        $this->assertEqualsCanonicalizing(
            ['admin', 'gerente', 'jefe_area', 'estandar'],
            Role::pluck('name')->all(),
        );
    }

    public static function managerRouteMatrix(): array
    {
        return [
            'admin' => ['admin', 200],
            'gerente' => ['gerente', 200],
            'jefe_area' => ['jefe_area', 403],
            'estandar' => ['estandar', 403],
        ];
    }

    #[DataProvider('managerRouteMatrix')]
    public function test_route_allows_only_listed_roles(string $role, int $status): void
    {
        $user = User::factory()->role($role)->create();

        $this->actingAs($user)->get('/_test/managers')->assertStatus($status);
    }

    public function test_route_without_role_list_defaults_to_admin(): void
    {
        $this->actingAs(User::factory()->role('admin')->create())
            ->get('/_test/admins')->assertOk();

        $this->actingAs(User::factory()->role('gerente')->create())
            ->get('/_test/admins')->assertForbidden();
    }

    public function test_factory_defaults_to_estandar(): void
    {
        $this->assertTrue(User::factory()->create()->hasRole('estandar'));
    }
}
