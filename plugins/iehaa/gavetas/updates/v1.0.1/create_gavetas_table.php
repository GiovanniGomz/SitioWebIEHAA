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

        Schema::dropIfExists('data.gavetas');

        Schema::create('data.gavetas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('url');

            $table->unsignedInteger('archivero_id');

            $table->foreign('archivero_id')
                ->references('id')
                ->on('data.archiveros');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data.gavetas');
    }
};
