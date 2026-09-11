<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFCorrespondencia', function () {
        return (new \Iehaa\Correspondencia\Components\CorrespondenciaComponent())->generarPdf();
    });

    Route::get('/reporteExcelCorrespondencia', function () {
        return (new \Iehaa\Correspondencia\Components\CorrespondenciaComponent())->generarExcel();
    });
});
