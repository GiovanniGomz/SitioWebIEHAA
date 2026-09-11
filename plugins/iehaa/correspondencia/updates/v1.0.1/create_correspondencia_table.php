<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('data.correspondencia');

        Schema::create('data.correspondencia', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('solicitud_id')->unique();
            $table->unsignedInteger('categoria_correspondencia_id');

            $table->string('remitente');
            $table->text('descripcion_documento')->nullable();
            $table->dateTime('fecha_atencion');

            $table->boolean('enviado_email')->default(false);
            $table->text('detalle_envio')->nullable();

            $table->timestamps();

            $table->foreign('solicitud_id')
                ->references('id')
                ->on('data.solicitudes')
                ->onDelete('cascade');

            $table->foreign('categoria_correspondencia_id')
                ->references('id')
                ->on('data.categorias_correspondencia');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.correspondencia');
    }
};
