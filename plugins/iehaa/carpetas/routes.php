<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFCarpeta', function () {
    return (new \IEHAA\Carpetas\Components\CarpetaComponent())->generarPdf();
});

Route::get('/reporteExcelCarpeta', function () {
    return (new \IEHAA\Carpetas\Components\CarpetaComponent())->generarExcel();
});
