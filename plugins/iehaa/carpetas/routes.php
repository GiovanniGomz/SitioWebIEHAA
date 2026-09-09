<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFCarpeta', function () {
        return (new \IEHAA\Carpetas\Components\CarpetaComponent())->generarPdf();
    });

    Route::get('/reporteExcelCarpeta', function () {
        return (new \IEHAA\Carpetas\Components\CarpetaComponent())->generarExcel();
    });
});
