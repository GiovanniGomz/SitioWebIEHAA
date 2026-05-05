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
        Schema::dropIfExists('data.documentos');

        Schema::create('data.documentos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('archivo');
            $table->string('peso');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iehaa_documentos_documentos');
    }
};
