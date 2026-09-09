<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('data.solicitudes');

        Schema::create('data.solicitudes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo');
            $table->unsignedInteger('documento_id');
            $table->string('nombre_solicitante');
            $table->string('email_solicitante');
            $table->string('telefono_solicitante')->nullable();
            $table->text('mensaje')->nullable();
            $table->string('estado')->default('pendiente');
            $table->text('admin_notas')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.solicitudes');
    }
};
