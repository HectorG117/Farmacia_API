<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id('id_medico');

            $table->unsignedBigInteger('id_usuario');
            $table->string('cedula', 30)->unique();
            $table->unsignedBigInteger('especialidad_id');
            $table->string('consultorio', 100);

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('cascade');

            $table->foreign('especialidad_id')
                ->references('id_especialidad')
                ->on('especialidades')
                ->onDelete('restrict');

            $table->unique('id_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};