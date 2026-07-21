<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueos_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
            $table->date('fecha_bloqueo');
            $table->time('hora_inicio_bloqueo')->nullable();
            $table->time('hora_fin_bloqueo')->nullable();
            $table->string('motivo')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueos_horario');
    }
};
