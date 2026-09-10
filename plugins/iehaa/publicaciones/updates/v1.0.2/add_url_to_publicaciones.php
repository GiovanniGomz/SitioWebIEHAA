<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('data.publicaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('data.publicaciones', 'url')) {
                $table->string('url', 500)->nullable()->after('archivo');
            }
        });
    }

    public function down()
    {
        Schema::table('data.publicaciones', function (Blueprint $table) {
            if (Schema::hasColumn('data.publicaciones', 'url')) {
                $table->dropColumn('url');
            }
        });
    }
};
