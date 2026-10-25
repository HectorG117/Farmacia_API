<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La lista de deseos se elimina: su función la cubren ahora las recetas médicas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('lista_deseos');
    }

    // Al revertir se vuelve a crear la tabla tal como estaba
    public function down(): void
    {
        if (Schema::hasTable('lista_deseos')) {
            return;
        }

        Schema::create('lista_deseos', function (Blueprint $table) {
            $table->id('id_lista');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_servicio');
            $table->dateTime('fecha_agregado')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios_medicos')->onDelete('cascade');
        });
    }
};
