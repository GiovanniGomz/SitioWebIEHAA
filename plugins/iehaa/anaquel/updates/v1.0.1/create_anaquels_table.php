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

        Schema::dropIfExists('data.anaqueles');

        Schema::create('data.anaqueles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('url');

            $table->unsignedInteger('estante_id');

            $table->foreign('estante_id')
                ->references('id')
                ->on('data.estantes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data.anaqueles');
    }
};
