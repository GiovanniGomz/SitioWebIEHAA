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

        Schema::dropIfExists('data.carpetas');

        Schema::create('data.carpetas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('url');

            $table->unsignedInteger('gaveta_id');

            $table->foreign('gaveta_id')
                ->references('id')
                ->on('data.gavetas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data.carpetas');
    }
};
