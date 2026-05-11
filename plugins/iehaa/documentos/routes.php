<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFDescarga', function () {
    return (new \IEHAA\Documentos\Components\DocumentoComponent())->generarPdf();
});

Route::get('/reporteExcelDescarga', function () {
    return (new \IEHAA\Documentos\Components\DocumentoComponent())->generarExcel();
});
