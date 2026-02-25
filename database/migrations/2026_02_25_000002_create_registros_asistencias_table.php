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
        Schema::create('registros_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_temporal_id')
                ->constrained('empleados_temporales')
                ->onDelete('cascade');
            $table->enum('tipo', ['asistencia', 'retardo', 'visita'])->default('asistencia');
            $table->timestamp('fecha_hora')->useCurrent();
            $table->timestamps();

            // Índice para acelerar las consultas por fecha
            $table->index(['empleado_temporal_id', 'fecha_hora']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_asistencias');
    }
};
