<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_sociales', function (Blueprint $table) {
            $table->id('id_sesion');

            $table->unsignedBigInteger('id_usuario');

            $table->string('proveedor', 50);
            $table->string('provider_id', 200);
            $table->dateTime('fecha_vinculacion')->useCurrent();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_sociales');
    }
};