<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use App\Services\RegisterCognitoUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with one demo user per role,
     * created in Cognito and mirrored locally via RegisterCognitoUser.
     */
    public function run(RegisterCognitoUser $registerCognitoUser): void
    {
        $area = Area::firstOrCreate(['name' => 'Tecnología']);

        $testUsers = [
            [
                'name' => 'Admin Demo',
                'email' => 'admin@example.com',
                'password' => 'Password123!',
                'rol' => 'admin',
                'area_id' => null,
            ],
            [
                'name' => 'Gerente Demo',
                'email' => 'gerente@example.com',
                'password' => 'Password123!',
                'rol' => 'gerente',
                'area_ids' => [$area->id],
            ],
            [
                'name' => 'Jefe de Área Demo',
                'email' => 'jefe.area@example.com',
                'password' => 'Password123!',
                'rol' => 'jefe_area',
                'area_id' => $area->id,
            ],
            [
                'name' => 'Usuario Estándar Demo',
                'email' => 'estandar@example.com',
                'password' => 'Password123!',
                'rol' => 'estandar',
                'area_id' => $area->id,
            ],
        ];

        foreach ($testUsers as $data) {
            if (User::where('email', $data['email'])->exists()) {
                $this->command?->info("Ya existe: {$data['email']}, se omite.");

                continue;
            }

            $result = $registerCognitoUser($data);

            if ($result['status'] === 'error') {
                $this->command?->error("No se pudo crear {$data['email']}: {$result['message']}");

                continue;
            }

            $this->command?->info("Usuario creado: {$data['email']} / {$data['password']}");
        }
    }
}
