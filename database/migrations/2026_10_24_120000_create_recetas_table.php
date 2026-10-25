<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo de recetas médicas.
 *
 * - recetas:         encabezado de la receta (médico que la emite, paciente, fecha, diagnóstico...)
 * - receta_detalle:  productos recetados con las instrucciones de uso indicadas por el médico
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id('id_receta');

            $table->unsignedBigInteger('medico_id');
            $table->unsignedBigInteger('paciente_id');

            $table->dateTime('fecha_emision')->useCurrent();
            $table->string('diagnostico', 255)->nullable();
            $table->text('indicaciones_generales')->nullable();
            $table->string('estado', 20)->default('Vigente'); // Vigente | Surtida | Cancelada

            $table->foreign('medico_id')
                ->references('id_medico')
                ->on('medicos')
                ->onDelete('restrict');

            $table->foreign('paciente_id')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('cascade');
        });

        Schema::create('receta_detalle', function (Blueprint $table) {
            $table->id('id_detalle');

            $table->unsignedBigInteger('id_receta');
            $table->unsignedBigInteger('producto_id');

            $table->unsignedInteger('cantidad')->default(1);
            $table->string('dosis', 100);        // Ej. "1 tableta de 500 mg"
            $table->string('frecuencia', 100);   // Ej. "Cada 8 horas"
            $table->string('duracion', 100);     // Ej. "Durante 5 días"
            $table->text('instrucciones')->nullable(); // Ej. "Tomar después de los alimentos"

            $table->foreign('id_receta')
                ->references('id_receta')
                ->on('recetas')
                ->onDelete('cascade');

            $table->foreign('producto_id')
                ->references('id')
                ->on('productos')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta_detalle');
        Schema::dropIfExists('recetas');
    }
};
