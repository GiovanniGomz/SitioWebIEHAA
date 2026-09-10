<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('data.expedientes');

        Schema::create('data.expedientes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('serie');
            $table->string('subserie')->nullable();
            $table->string('asunto');

            // Fechas extremas
            $table->date('fecha_inicial')->nullable();
            $table->date('fecha_final')->nullable();

            // Unidad de instalación
            $table->string('unidad_instalacion');
            $table->string('unidad_instalacion_otro')->nullable();

            $table->integer('cantidad_folios')->nullable();
            $table->string('volumen')->nullable();          // metros lineales
            $table->string('soporte')->nullable();

            // Formato del documento
            $table->string('formato')->default('impreso');  // impreso | digital
            $table->string('archivo')->nullable();

            $table->string('estado_conservacion')->nullable();

            // Signatura topográfica
            $table->string('sig_fila')->nullable();
            $table->string('sig_estante')->nullable();
            $table->string('sig_anaquel')->nullable();
            $table->string('sig_posicion')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.expedientes');
    }
};
