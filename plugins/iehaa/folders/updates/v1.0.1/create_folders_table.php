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
        Schema::dropIfExists('data.fabio');

        Schema::dropIfExists('data.folders');

        Schema::create('data.folders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('url');

            $table->unsignedInteger('carpeta_id');

            $table->foreign('carpeta_id')
                ->references('id')
                ->on('data.carpetas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data.folders');
    }
};
