<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verificaciones_cedula', function (Blueprint $table) {
            $table->id();
            $table->string('numero_cedula')->unique();
            $table->string('nombre_titular');
            $table->string('profesion');
            $table->string('institucion')->nullable();
            $table->boolean('es_valida')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificaciones_cedula');
    }
};
