<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFTipoPublicaciones', function () {
        return (new \IEHAA\Tipopublicaciones\Components\TipoPublicacionComponent())->generarPdf();
    });

    Route::get('/reporteExcelTipoPublicaciones', function () {
        return (new \IEHAA\Tipopublicaciones\Components\TipoPublicacionComponent())->generarExcel();
    });
});
