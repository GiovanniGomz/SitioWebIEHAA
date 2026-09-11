<?php

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

/**
 * Campos para "olvidé mi contraseña" (token hasheado + vencimiento) y para
 * el perfil del usuario (foto).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('data.usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('data.usuarios', 'reset_token')) {
                $table->string('reset_token')->nullable();
            }
            if (!Schema::hasColumn('data.usuarios', 'reset_token_expira')) {
                $table->timestamp('reset_token_expira')->nullable();
            }
            if (!Schema::hasColumn('data.usuarios', 'foto')) {
                $table->string('foto')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('data.usuarios', function (Blueprint $table) {
            foreach (['reset_token', 'reset_token_expira', 'foto'] as $col) {
                if (Schema::hasColumn('data.usuarios', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
