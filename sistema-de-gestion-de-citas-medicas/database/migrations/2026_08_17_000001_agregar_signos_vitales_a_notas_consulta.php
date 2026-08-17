<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas_consulta', function (Blueprint $table) {
            $table->string('presion_arterial', 20)->nullable()->after('cita_id');
            $table->integer('frecuencia_cardiaca')->nullable()->after('presion_arterial');
            $table->string('temperatura', 10)->nullable()->after('frecuencia_cardiaca');
            $table->string('peso', 10)->nullable()->after('temperatura');
        });
    }

    public function down(): void
    {
        Schema::table('notas_consulta', function (Blueprint $table) {
            $table->dropColumn(['presion_arterial', 'frecuencia_cardiaca', 'temperatura', 'peso']);
        });
    }
};
