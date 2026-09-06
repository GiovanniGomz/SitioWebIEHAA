<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFAnaquel', function () {
    return (new \IEHAA\Anaquel\Components\AnaquelComponent())->generarPdf();
});

Route::get('/reporteExcelAnaquel', function () {
    return (new \IEHAA\Anaquel\Components\AnaquelComponent())->generarExcel();
});
