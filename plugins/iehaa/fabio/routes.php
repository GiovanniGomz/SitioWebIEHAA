<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFFabio', function () {
    return (new \IEHAA\Fabio\Components\FabioComponent())->generarPdf();
});

Route::get('/reporteExcelFabio', function () {
    return (new \IEHAA\Fabio\Components\FabioComponent())->generarExcel();
});
