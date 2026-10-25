<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrito_detalle', function (Blueprint $table) {
            $table->id('id_detalle');

            $table->unsignedBigInteger('id_carrito');
            $table->unsignedBigInteger('id_servicio');

            $table->integer('cantidad')->default(1);

            $table->foreign('id_carrito')
                ->references('id_carrito')
                ->on('carrito')
                ->onDelete('cascade');

            $table->foreign('id_servicio')
                ->references('id_servicio')
                ->on('servicios_medicos')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrito_detalle');
    }
};