<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFTipoPublicaciones', function () {
    return (new \IEHAA\Tipopublicaciones\Components\TipoPublicacionComponent())->generarPdf();
});

/*
Route::get('/reporteExcelDescarga', function () {
    return (new \IEHAA\Documentos\Components\DocumentoComponent())->generarExcel();
});*/
