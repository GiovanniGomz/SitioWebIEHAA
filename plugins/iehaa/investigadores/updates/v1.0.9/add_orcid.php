<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

/**
 * Código ORCID del investigador (identificador científico internacional,
 * formato 0000-0000-0000-0000). Se muestra en la página pública.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('data.investigadores', function (Blueprint $table) {
            if (!Schema::hasColumn('data.investigadores', 'orcid')) {
                $table->string('orcid', 19)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('data.investigadores', function (Blueprint $table) {
            if (Schema::hasColumn('data.investigadores', 'orcid')) {
                $table->dropColumn('orcid');
            }
        });
    }
};
