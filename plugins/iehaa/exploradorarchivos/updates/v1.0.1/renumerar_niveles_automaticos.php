<?php

use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\DB;

/**
 * Los archiveros / gavetas / estantes / anaqueles ahora se crean con un
 * código correlativo automático (1, 2, 3...). Esta migración normaliza los
 * registros existentes al mismo esquema:
 *   - archiveros y estantes: 1..N por orden de creación (id)
 *   - gavetas: 1..N dentro de cada archivero
 *   - anaqueles: 1..N dentro de cada estante
 */
return new class extends Migration
{
    public function up()
    {
        $this->renumerarPlano('data.archiveros');
        $this->renumerarPlano('data.estantes');
        $this->renumerarPorPadre('data.gavetas', 'archivero_id');
        $this->renumerarPorPadre('data.anaqueles', 'estante_id');
    }

    public function down()
    {
        // Sin reversa: no guardamos los códigos anteriores.
    }

    private function renumerarPlano(string $tabla): void
    {
        $i = 1;

        foreach (DB::table($tabla)->orderBy('id')->pluck('id') as $id) {
            DB::table($tabla)->where('id', $id)->update(['codigo' => (string) $i++]);
        }
    }

    private function renumerarPorPadre(string $tabla, string $fk): void
    {
        $padres = DB::table($tabla)->distinct()->pluck($fk);

        foreach ($padres as $padre) {
            $i = 1;

            foreach (DB::table($tabla)->where($fk, $padre)->orderBy('id')->pluck('id') as $id) {
                DB::table($tabla)->where('id', $id)->update(['codigo' => (string) $i++]);
            }
        }
    }
};
