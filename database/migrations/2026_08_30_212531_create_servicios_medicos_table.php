<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios_medicos', function (Blueprint $table) {
            $table->id('id_servicio');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('costo', 10, 2);
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios_medicos');
    }
};