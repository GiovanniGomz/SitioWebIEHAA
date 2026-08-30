<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFFacultades', function () {
    return (new \IEHAA\Facultades\Components\FacultadComponent())->generarPdf();
});

Route::get('/reporteExcelFacultades', function () {
    return (new \IEHAA\Facultades\Components\FacultadComponent())->generarExcel();
});
