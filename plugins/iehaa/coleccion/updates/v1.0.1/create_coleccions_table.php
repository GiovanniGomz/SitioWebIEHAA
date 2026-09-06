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

        Schema::dropIfExists('data.colecciones');

        Schema::create('data.colecciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('url');

            $table->unsignedInteger('anaquel_id');

            $table->foreign('anaquel_id')
                ->references('id')
                ->on('data.anaqueles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data.coleccion');
    }
};
