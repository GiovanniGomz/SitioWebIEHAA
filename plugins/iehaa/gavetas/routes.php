<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFGaveta', function () {
    return (new \IEHAA\Gavetas\Components\GavetaComponent())->generarPdf();
});

Route::get('/reporteExcelGaveta', function () {
    return (new \IEHAA\Gavetas\Components\GavetaComponent())->generarExcel();
});
