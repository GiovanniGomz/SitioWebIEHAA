<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('data.proyectos');

        Schema::create('data.proyectos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->text('descripcion');
            $table->text('detalle')->nullable();

            $table->unsignedInteger('investigador_id');

            $table->foreign('investigador_id')
                ->references('id')
                ->on('data.investigadores');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.proyectos');
    }
};
