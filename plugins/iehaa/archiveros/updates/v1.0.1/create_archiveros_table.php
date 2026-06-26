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

        Schema::dropIfExists('data.archiveros');

        Schema::create('data.archiveros', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data.archiveros');
    }
};
