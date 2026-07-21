<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_doctor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
            $table->enum('dia_semana', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('duracion_consulta_minutos')->default(30);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_doctor');
    }
};
