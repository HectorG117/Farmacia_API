<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id('id_cita');

            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('medico_id');

            $table->date('fecha');
            $table->time('hora');
            $table->string('estado', 30)->default('Pendiente');
            $table->text('motivo')->nullable();

            $table->foreign('paciente_id')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('cascade');

            $table->foreign('medico_id')
                ->references('id_medico')
                ->on('medicos')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};