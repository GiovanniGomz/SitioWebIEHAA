<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFFolder', function () {
    return (new \IEHAA\Folders\Components\FolderComponent())->generarPdf();
});

Route::get('/reporteExcelFolder', function () {
    return (new \IEHAA\Folders\Components\FolderComponent())->generarExcel();
});
