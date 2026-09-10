<?php

use Iehaa\Configuracion\Models\Configuracion;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Support\Facades\Schema;

/**
 * Campos editables de la página pública desde el módulo de Configuración:
 * texto "Sobre nosotros", sección "Conservemos y estudiamos el patrimonio...",
 * mapa de contacto y redes sociales.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('data.configuracion', function (Blueprint $table) {
            if (!Schema::hasColumn('data.configuracion', 'texto_nosotros')) {
                $table->text('texto_nosotros')->nullable();
            }
            if (!Schema::hasColumn('data.configuracion', 'titulo_patrimonio')) {
                $table->string('titulo_patrimonio')->nullable();
            }
            if (!Schema::hasColumn('data.configuracion', 'texto_patrimonio')) {
                $table->text('texto_patrimonio')->nullable();
            }
            if (!Schema::hasColumn('data.configuracion', 'video_url')) {
                $table->string('video_url', 500)->nullable();
            }
            if (!Schema::hasColumn('data.configuracion', 'mapa_embed')) {
                $table->text('mapa_embed')->nullable();
            }
            if (!Schema::hasColumn('data.configuracion', 'facebook_url')) {
                $table->string('facebook_url', 500)->nullable();
            }
            if (!Schema::hasColumn('data.configuracion', 'instagram_url')) {
                $table->string('instagram_url', 500)->nullable();
            }
        });

        $config = Configuracion::actual();

        $config->texto_nosotros = $config->texto_nosotros ?: implode("\n\n", [
            'El Instituto de Estudios Históricos, Antropológicos y Arqueológicos (IEHAA) de la Universidad de El Salvador es un espacio dedicado a la investigación, documentación y preservación del patrimonio histórico y cultural salvadoreño, impulsando el conocimiento científico en historia, antropología y arqueología.',
            'A través de sus investigadores y sus fondos documentales — Fabio Castillo y el Fondo Bibliográfico — el instituto resguarda y pone a disposición del público materiales históricos, promoviendo la divulgación científica y el acceso al patrimonio cultural de la nación.',
        ]);

        $config->titulo_patrimonio = $config->titulo_patrimonio
            ?: 'Conservamos y estudiamos el patrimonio histórico de El Salvador.';

        $config->texto_patrimonio = $config->texto_patrimonio
            ?: 'A través de la investigación científica documentamos, resguardamos y difundimos nuestra memoria histórica.';

        $config->video_url = $config->video_url ?: 'https://www.youtube.com/watch?v=Y7f98aduVJ8';

        $config->mapa_embed = $config->mapa_embed
            ?: 'https://maps.google.com/maps?q=Universidad%20de%20El%20Salvador%2C%20San%20Salvador&t=&z=15&ie=UTF8&iwloc=&output=embed';

        $config->facebook_url = $config->facebook_url ?: 'https://www.facebook.com/';
        $config->instagram_url = $config->instagram_url ?: 'https://www.instagram.com/';

        $config->save();
    }

    public function down()
    {
        Schema::table('data.configuracion', function (Blueprint $table) {
            foreach (['texto_nosotros', 'titulo_patrimonio', 'texto_patrimonio', 'video_url', 'mapa_embed', 'facebook_url', 'instagram_url'] as $col) {
                if (Schema::hasColumn('data.configuracion', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
