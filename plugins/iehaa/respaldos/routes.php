<?php

use Iehaa\Bitacora\Models\Bitacora;
use Iehaa\Respaldos\Classes\ServicioRespaldo;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    // Descarga de un respaldo para guardarlo en cualquier lugar de la PC.
    // Ruta de Laravel (no del CMS): si falla, se redirige con un aviso.
    Route::get('/respaldos/descargar/{archivo}', function (string $archivo) {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $ruta = ServicioRespaldo::rutaDe($archivo);

        if (!$ruta) {
            return redirect('/respaldos')->with('error_descarga', 'El respaldo ya no está disponible.');
        }

        Bitacora::registrar('descarga', 'Respaldos', "Descargó el respaldo {$archivo}");

        return response()->download($ruta, $archivo, ['Content-Type' => 'application/octet-stream']);
    })->where('archivo', '[A-Za-z0-9_.\-]+');
});
