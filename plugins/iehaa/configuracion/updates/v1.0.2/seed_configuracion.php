<?php

use Iehaa\Configuracion\Models\Configuracion;
use Winter\Storm\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Configuracion::actual();
    }

    public function down()
    {
        Configuracion::query()->delete();
    }
};
