<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('data.publicaciones');

        Schema::create('data.publicaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('archivo')->nullable();
            $table->date('fecha')->nullable();

            $table->unsignedInteger('tipo_publicacion_id');
            $table->unsignedInteger('investigador_id');

            $table->foreign('tipo_publicacion_id')
                ->references('id')
                ->on('data.tipo_publicaciones');

            $table->foreign('investigador_id')
                ->references('id')
                ->on('data.investigadores');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.publicaciones');
    }
};
