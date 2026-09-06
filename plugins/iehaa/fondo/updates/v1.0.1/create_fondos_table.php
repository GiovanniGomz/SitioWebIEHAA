<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('data.fondo');

        Schema::create('data.fondo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('archivo');

            $table->unsignedInteger('coleccion_id');

            $table->foreign('coleccion_id')
                ->references('id')
                ->on('data.colecciones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data.fondo');
    }
};
