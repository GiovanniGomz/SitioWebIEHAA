<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFExpedientes', function () {
        return (new \Iehaa\Gestiondocumental\Components\ExpedienteComponent())->generarPdf();
    });

    Route::get('/reporteExcelExpedientes', function () {
        return (new \Iehaa\Gestiondocumental\Components\ExpedienteComponent())->generarExcel();
    });

    Route::get('/descarga-expediente/{id}', function ($id) {
        return (new \Iehaa\Gestiondocumental\Components\ExpedienteComponent())->descargar($id);
    })->where('id', '[0-9]+');
});
