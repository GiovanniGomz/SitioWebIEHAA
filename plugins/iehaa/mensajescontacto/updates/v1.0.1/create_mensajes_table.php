<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('data.mensajes_contacto');

        Schema::create('data.mensajes_contacto', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('email');
            $table->string('asunto');
            $table->text('mensaje');
            $table->boolean('leido')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data.mensajes_contacto');
    }
};
