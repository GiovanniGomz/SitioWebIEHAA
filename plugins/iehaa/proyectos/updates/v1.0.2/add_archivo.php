<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

/**
 * Documento del proyecto, descargable desde la página pública de
 * investigaciones.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('data.proyectos', function (Blueprint $table) {
            if (!Schema::hasColumn('data.proyectos', 'archivo')) {
                $table->string('archivo')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('data.proyectos', function (Blueprint $table) {
            if (Schema::hasColumn('data.proyectos', 'archivo')) {
                $table->dropColumn('archivo');
            }
        });
    }
};
