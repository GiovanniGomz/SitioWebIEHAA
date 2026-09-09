<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFAnaquel', function () {
        return (new \IEHAA\Anaquel\Components\AnaquelComponent())->generarPdf();
    });

    Route::get('/reporteExcelAnaquel', function () {
        return (new \IEHAA\Anaquel\Components\AnaquelComponent())->generarExcel();
    });
});
