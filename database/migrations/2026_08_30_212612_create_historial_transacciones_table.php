<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_transacciones', function (Blueprint $table) {
            $table->id('id_transaccion');

            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_carrito');

            $table->decimal('total', 10, 2);
            $table->string('estado', 30);
            $table->dateTime('fecha')->useCurrent();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('cascade');

            $table->foreign('id_carrito')
                ->references('id_carrito')
                ->on('carrito')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_transacciones');
    }
};