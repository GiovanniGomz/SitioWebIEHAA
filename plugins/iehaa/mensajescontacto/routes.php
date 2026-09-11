<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFMensajesContacto', function () {
        return (new \Iehaa\Mensajescontacto\Components\MensajeContactoComponent())->generarPdf();
    });

    Route::get('/reporteExcelMensajesContacto', function () {
        return (new \Iehaa\Mensajescontacto\Components\MensajeContactoComponent())->generarExcel();
    });
});
