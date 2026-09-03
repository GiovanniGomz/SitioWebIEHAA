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
        Schema::dropIfExists('data.investigadores');

        Schema::create('data.investigadores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('carnet');
            $table->string('email');
            $table->string('telefono');

            $table->unsignedInteger('facultad_id');
            $table->unsignedInteger('tipo_investigador_id');
            $table->unsignedInteger('categoria_investigador_id');

            $table->string('sexo');
            $table->string('publicaciones');
            $table->text('descripcion');

            $table->foreign('facultad_id')
                ->references('id')
                ->on('data.facultades');

            $table->foreign('tipo_investigador_id')
                ->references('id')
                ->on('data.tipo_investigadores');

            $table->foreign('categoria_investigador_id')
                ->references('id')
                ->on('data.categoria_investigadores');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data.investigadores');
    }
};
