<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFFondo', function () {
    return (new \IEHAA\Fondo\Components\FondoComponent())->generarPdf();
});

Route::get('/reporteExcelFondo', function () {
    return (new \IEHAA\Fondo\Components\FondoComponent())->generarExcel();
});
