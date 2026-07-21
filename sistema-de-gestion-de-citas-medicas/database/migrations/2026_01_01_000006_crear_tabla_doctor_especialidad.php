<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_especialidad', function (Blueprint $table) {
            $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->primary(['perfil_doctor_id', 'especialidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_especialidad');
    }
};
