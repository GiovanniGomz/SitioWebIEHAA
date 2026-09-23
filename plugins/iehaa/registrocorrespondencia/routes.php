<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFRegistroCorrespondencia', function () {
        return (new \Iehaa\Registrocorrespondencia\Components\RegistroCorrespondenciaComponent())->generarPdf();
    });

    Route::get('/reporteExcelRegistroCorrespondencia', function () {
        return (new \Iehaa\Registrocorrespondencia\Components\RegistroCorrespondenciaComponent())->generarExcel();
    });

    Route::get('/descarga-correspondencia/{id}', function ($id) {
        return (new \Iehaa\Registrocorrespondencia\Components\RegistroCorrespondenciaComponent())->descargar($id);
    })->where('id', '[0-9]+');
});
