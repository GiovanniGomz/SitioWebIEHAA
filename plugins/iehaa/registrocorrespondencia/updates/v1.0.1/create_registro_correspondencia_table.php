<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('data.registro_correspondencia');

        Schema::create('data.registro_correspondencia', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 150);
            $table->integer('facultad_id')->unsigned();
            // 'enviado' | 'recibido'
            $table->string('tipo', 10);
            $table->date('fecha');
            $table->string('archivo');
            $table->integer('categoria_correspondencia_id')->unsigned();
            $table->timestamps();

            $table->foreign('facultad_id')->references('id')->on('data.facultades');
            $table->foreign('categoria_correspondencia_id')->references('id')->on('data.categorias_correspondencia');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.registro_correspondencia');
    }
};
