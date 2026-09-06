<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFEstante', function () {
    return (new \IEHAA\Estante\Components\EstanteComponent())->generarPdf();
});

Route::get('/reporteExcelEstante', function () {
    return (new \IEHAA\Estante\Components\EstanteComponent())->generarExcel();
});
