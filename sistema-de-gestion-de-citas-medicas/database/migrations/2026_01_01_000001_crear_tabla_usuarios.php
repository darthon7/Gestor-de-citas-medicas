<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('curp', 18)->unique()->nullable();
            $table->string('telefono', 20)->nullable();
            $table->enum('rol', ['admin', 'doctor', 'recepcionista', 'paciente']);
            $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])->default('activo');
            $table->string('foto_perfil')->nullable();
            $table->integer('intentos_fallidos')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
