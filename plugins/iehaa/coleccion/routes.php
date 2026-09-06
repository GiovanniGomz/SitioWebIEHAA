<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFColeccion', function () {
    return (new \IEHAA\Coleccion\Components\ColeccionComponent())->generarPdf();
});

Route::get('/reporteExcelColeccion', function () {
    return (new \IEHAA\Coleccion\Components\ColeccionComponent())->generarExcel();
});
