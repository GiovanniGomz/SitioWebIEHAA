<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFFondo', function () {
        return (new \IEHAA\Fondo\Components\FondoComponent())->generarPdf();
    });

    Route::get('/reporteExcelFondo', function () {
        return (new \IEHAA\Fondo\Components\FondoComponent())->generarExcel();
    });
});
