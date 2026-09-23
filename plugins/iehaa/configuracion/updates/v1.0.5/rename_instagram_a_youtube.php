<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

/**
 * Se reemplaza el enlace de Instagram por el de YouTube en la configuración
 * del sitio (conserva el valor que hubiera cargado, si lo hay).
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('data.configuracion', 'instagram_url') && !Schema::hasColumn('data.configuracion', 'youtube_url')) {
            Schema::table('data.configuracion', function (Blueprint $table) {
                $table->renameColumn('instagram_url', 'youtube_url');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('data.configuracion', 'youtube_url') && !Schema::hasColumn('data.configuracion', 'instagram_url')) {
            Schema::table('data.configuracion', function (Blueprint $table) {
                $table->renameColumn('youtube_url', 'instagram_url');
            });
        }
    }
};
