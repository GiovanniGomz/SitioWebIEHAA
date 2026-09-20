<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('data.bitacora');

        Schema::create('data.bitacora', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('usuario_id')->nullable();
            // Se guardan nombre y correo "congelados": la bitácora debe seguir
            // siendo legible aunque el usuario se elimine después.
            $table->string('usuario_nombre', 150)->nullable();
            $table->string('usuario_email', 150)->nullable();
            $table->string('accion', 30)->index();
            $table->string('modulo', 80)->index();
            $table->text('descripcion');
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.bitacora');
    }
};
