<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reference roles. They are inserted here (not in a seeder) because
     * the users backfill below needs them to exist.
     */
    private const ROLES = [
        'admin' => 'Administrador',
        'gerente' => 'Gerente',
        'jefe_area' => 'Jefe de área',
        'estandar' => 'Usuario estándar',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->timestamps();
        });

        $now = now();

        DB::table('roles')->insert(array_map(
            fn (string $name, string $label) => ['name' => $name, 'label' => $label, 'created_at' => $now, 'updated_at' => $now],
            array_keys(self::ROLES),
            self::ROLES,
        ));

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained('roles');
        });

        DB::statement('UPDATE users SET role_id = (SELECT id FROM roles WHERE roles.name = users.role)');

        DB::table('users')
            ->whereNull('role_id')
            ->update(['role_id' => DB::table('roles')->where('name', 'estandar')->value('id')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', array_keys(self::ROLES))->default('estandar');
        });

        DB::statement('UPDATE users SET role = COALESCE((SELECT name FROM roles WHERE roles.id = users.role_id), \'estandar\')');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
