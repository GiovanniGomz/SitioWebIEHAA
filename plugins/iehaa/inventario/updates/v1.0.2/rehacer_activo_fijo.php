<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

/**
 * El plugin "inventario" era un scaffold vacío sin usar. Se reutiliza para
 * el módulo de Inventario de Activo Fijo.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('iehaa_inventario_inventarios');
        Schema::dropIfExists('data.activo_fijo_archivos');
        Schema::dropIfExists('data.activo_fijo');

        Schema::create('data.activo_fijo', function (Blueprint $table) {
            $table->increments('id');

            $table->string('numero_inventario')->unique();
            $table->string('descripcion_bien');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('serie')->nullable();

            $table->string('responsable');

            $table->string('estado_bien');       // condición física: Bueno / Regular / Malo
            $table->string('estado');             // situación administrativa: Activo / Descargado / etc.

            $table->text('descripcion')->nullable();
            $table->text('observacion')->nullable();

            $table->string('forma_adquisicion');  // Compra / Donación / Otro
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('precio', 10, 2)->nullable();

            $table->timestamps();
        });

        Schema::create('data.activo_fijo_archivos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('activo_fijo_id');
            $table->string('archivo');
            $table->string('nombre_original')->nullable();
            $table->timestamps();

            $table->foreign('activo_fijo_id')
                ->references('id')
                ->on('data.activo_fijo')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.activo_fijo_archivos');
        Schema::dropIfExists('data.activo_fijo');
    }
};
