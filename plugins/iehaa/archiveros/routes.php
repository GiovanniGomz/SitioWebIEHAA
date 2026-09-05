<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFArchivero', function () {
    return (new \IEHAA\Archiveros\Components\ArchiveroComponent())->generarPdf();
});

Route::get('/reporteExcelArchivero', function () {
    return (new \IEHAA\Archiveros\Components\ArchiveroComponent())->generarExcel();
});
