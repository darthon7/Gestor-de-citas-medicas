<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles_doctor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('cedula_profesional')->unique();
            $table->string('cedula_especialidad')->nullable();
            $table->enum('estado_validacion', ['pendiente', 'validado', 'rechazado'])->default('pendiente');
            $table->text('notas_validacion')->nullable();
            $table->foreignId('validado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('validado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_doctor');
    }
};
