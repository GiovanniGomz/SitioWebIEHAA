<?php

use Iehaa\Usuarios\Models\Usuario;
use Winter\Storm\Database\Updates\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Usuario::where('email', 'admin@iehaa.com')->exists()) {
            return;
        }

        $usuario = new Usuario();
        $usuario->nombre = 'Administrador';
        $usuario->email = 'admin@iehaa.com';
        $usuario->password = 'admin123';
        $usuario->rol = 'admin';
        $usuario->activo = true;
        $usuario->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Usuario::where('email', 'admin@iehaa.com')->delete();
    }
};
