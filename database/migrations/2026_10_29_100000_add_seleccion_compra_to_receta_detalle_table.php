<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El paciente (cliente) puede elegir qué productos de su receta quiere comprar
 * y en qué cantidad. La cantidad nunca supera la cantidad recetada por el médico.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receta_detalle', function (Blueprint $table) {
            $table->boolean('comprar')->default(false)->after('instrucciones');
            $table->unsignedInteger('cantidad_compra')->default(0)->after('comprar');
        });
    }

    public function down(): void
    {
        Schema::table('receta_detalle', function (Blueprint $table) {
            $table->dropColumn(['comprar', 'cantidad_compra']);
        });
    }
};
