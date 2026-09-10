<?php

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\DB;

/**
 * El listado de publicaciones dejó de manejarse dentro del módulo de
 * investigadores (ahora hay un módulo propio de Publicaciones), así que la
 * columna deja de ser obligatoria.
 */
return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE data.investigadores ALTER COLUMN publicaciones DROP NOT NULL");
        DB::statement("ALTER TABLE data.investigadores ALTER COLUMN publicaciones SET DEFAULT NULL");
    }

    public function down()
    {
        DB::statement("UPDATE data.investigadores SET publicaciones = '' WHERE publicaciones IS NULL");
        DB::statement("ALTER TABLE data.investigadores ALTER COLUMN publicaciones SET NOT NULL");
    }
};
