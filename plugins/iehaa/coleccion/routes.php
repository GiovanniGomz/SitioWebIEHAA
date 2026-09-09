<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFColeccion', function () {
        return (new \IEHAA\Coleccion\Components\ColeccionComponent())->generarPdf();
    });

    Route::get('/reporteExcelColeccion', function () {
        return (new \IEHAA\Coleccion\Components\ColeccionComponent())->generarExcel();
    });
});
