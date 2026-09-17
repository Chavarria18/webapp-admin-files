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
      
        Schema::create('gerente_areas', function (Blueprint $table) { 
            $table->id(); 
            $table->foreignId('gerente_id')->constrained('users')->cascadeOnDelete(); 
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->unique(['gerente_id', 'area_id']);
            
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gerente_areas');
    }
};
