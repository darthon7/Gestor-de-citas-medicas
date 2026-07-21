<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_paciente_id')->constrained('perfiles_paciente')->onDelete('cascade');
            $table->foreignId('perfil_doctor_id')->constrained('perfiles_doctor')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->string('codigo_referencia')->unique();
            $table->date('fecha_cita');
            $table->time('hora_cita');
            $table->integer('duracion_minutos')->default(30);
            $table->enum('estado', ['agendada', 'confirmada', 'en_consulta', 'completada', 'cancelada'])->default('agendada');
            $table->text('motivo_cancelacion')->nullable();
            $table->foreignId('cancelado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('cancelado_en')->nullable();
            $table->timestamp('checkin_en')->nullable();
            $table->foreignId('checkin_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
