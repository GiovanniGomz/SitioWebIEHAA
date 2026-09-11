<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

/**
 * Fondo de pantalla del hero de la página pública, editable desde
 * Configuración → Página pública.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('data.configuracion', function (Blueprint $table) {
            if (!Schema::hasColumn('data.configuracion', 'fondo_pantalla')) {
                $table->string('fondo_pantalla')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('data.configuracion', function (Blueprint $table) {
            if (Schema::hasColumn('data.configuracion', 'fondo_pantalla')) {
                $table->dropColumn('fondo_pantalla');
            }
        });
    }
};
