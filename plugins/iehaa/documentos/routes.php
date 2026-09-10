<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFDescarga', function () {
        return (new \IEHAA\Documentos\Components\DocumentoComponent())->generarPdf();
    });

    Route::get('/reporteExcelDescarga', function () {
        return (new \IEHAA\Documentos\Components\DocumentoComponent())->generarExcel();
    });

    Route::get('/descarga-documento/{id}', function ($id) {
        return (new \IEHAA\Documentos\Components\DocumentoComponent())->descargar($id);
    })->where('id', '[0-9]+');
});
