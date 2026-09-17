<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historials', function (Blueprint $table) {
            $table->string('action')->after('id');
            $table->foreignId('user_id')->after('action')->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('action');
        });
    }
};
