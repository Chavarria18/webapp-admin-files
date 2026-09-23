<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class UserHasRoleTest extends TestCase
{
    public function test_matches_the_users_role_name(): void
    {
        $user = $this->makeUser('gerente');

        $this->assertTrue($user->hasRole('gerente'));
        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_matches_any_of_several_role_names(): void
    {
        $user = $this->makeUser('jefe_area');

        $this->assertTrue($user->hasRole('admin', 'gerente', 'jefe_area'));
        $this->assertFalse($user->hasRole('admin', 'gerente'));
    }

    public function test_user_without_a_role_has_none(): void
    {
        $user = new User;
        $user->setRelation('role', null);

        $this->assertFalse($user->hasRole('estandar'));
    }

    private function makeUser(string $role): User
    {
        $user = new User;
        $user->setRelation('role', (new Role)->forceFill(['name' => $role]));

        return $user;
    }
}
