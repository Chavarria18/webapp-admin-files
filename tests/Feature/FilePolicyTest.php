<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\File;
use App\Models\Role;
use App\Models\User;
use App\Policies\FilePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Runs against the permission matrix seeded by the migrations, so it also
 * proves the default grants reproduce the original hard-coded rules.
 */
class FilePolicyTest extends TestCase
{
    use RefreshDatabase;

    private const AREA_A = 1;

    private const AREA_B = 2;

    private FilePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new FilePolicy();
    }

    /**
     * Each row: role of the actor, whose file it is, can view, can delete.
     *
     * The actor always belongs to area A (the gerente manages area A only).
     *  - own:        the actor uploaded the file
     *  - same_area:  a colleague from area A uploaded it
     *  - other_area: someone from area B uploaded it
     */
    public static function accessMatrix(): array
    {
        return [
            'estandar / own file' => ['estandar', 'own', true, true],
            'estandar / same area file' => ['estandar', 'same_area', false, false],
            'estandar / other area file' => ['estandar', 'other_area', false, false],

            'jefe_area / own file' => ['jefe_area', 'own', true, true],
            'jefe_area / same area file' => ['jefe_area', 'same_area', true, false],
            'jefe_area / other area file' => ['jefe_area', 'other_area', false, false],

            'gerente / managed area file' => ['gerente', 'same_area', true, true],
            'gerente / unmanaged area file' => ['gerente', 'other_area', false, false],

            'admin / own file' => ['admin', 'own', true, true],
            'admin / same area file' => ['admin', 'same_area', true, true],
            'admin / other area file' => ['admin', 'other_area', true, true],
        ];
    }

    #[DataProvider('accessMatrix')]
    public function test_view_and_delete_follow_role_rules(string $role, string $owner, bool $canView, bool $canDelete): void
    {
        $actor = $this->makeUser(1, $role);
        $file = $this->makeFile($actor, $owner);

        $this->assertSame($canView, $this->policy->view($actor, $file), "view as {$role} on {$owner}");
        $this->assertSame($canView, $this->policy->update($actor, $file), "update as {$role} on {$owner}");
        $this->assertSame($canDelete, $this->policy->delete($actor, $file), "delete as {$role} on {$owner}");
    }

    #[DataProvider('accessMatrix')]
    public function test_restore_and_force_delete_match_delete(string $role, string $owner, bool $canView, bool $canDelete): void
    {
        $actor = $this->makeUser(1, $role);
        $file = $this->makeFile($actor, $owner);

        $this->assertSame($canDelete, $this->policy->restore($actor, $file), "restore as {$role} on {$owner}");
        $this->assertSame($canDelete, $this->policy->forceDelete($actor, $file), "forceDelete as {$role} on {$owner}");
    }

    public function test_every_role_can_create_files(): void
    {
        foreach (['estandar', 'jefe_area', 'gerente', 'admin'] as $role) {
            $this->assertTrue($this->policy->create($this->makeUser(1, $role)), "create as {$role}");
        }
    }

    public function test_view_any_is_denied(): void
    {
        $this->assertFalse($this->policy->viewAny($this->makeUser(1, 'admin')));
    }

    /**
     * Build an unsaved user holding the seeded role (and its permissions).
     * Everyone lives in area A, except the gerente, who manages area A only.
     */
    private function makeUser(int $id, string $role, int $areaId = self::AREA_A): User
    {
        $user = (new User())->forceFill([
            'id' => $id,
            'area_id' => $role === 'gerente' ? null : $areaId,
        ]);

        $user->setRelation('role', Role::firstWhere('name', $role));

        $managed = $role === 'gerente'
            ? [(new Area())->forceFill(['id' => self::AREA_A])]
            : [];

        $user->setRelation('areasGestionadas', $user->newCollection($managed));

        return $user;
    }

    private function makeFile(User $actor, string $owner): File
    {
        $fileOwner = match ($owner) {
            'own' => $actor,
            'same_area' => $this->makeUser(99, 'estandar', self::AREA_A),
            'other_area' => $this->makeUser(99, 'estandar', self::AREA_B),
        };

        $file = (new File())->forceFill(['user_id' => $fileOwner->id]);
        $file->setRelation('user', $fileOwner);

        return $file;
    }
}
