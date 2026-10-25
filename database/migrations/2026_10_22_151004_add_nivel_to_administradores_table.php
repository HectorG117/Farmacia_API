<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administradores', function (Blueprint $table) {
            // 1: Lectura, 2: Crear y Editar, 3: Full (Crear, Editar, Eliminar y Cuentas)
            $table->tinyInteger('nivel')->default(1)->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('administradores', function (Blueprint $table) {
            $table->dropColumn('nivel');
        });
    }
};