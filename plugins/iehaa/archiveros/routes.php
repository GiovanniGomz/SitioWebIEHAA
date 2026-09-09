<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFArchivero', function () {
        return (new \IEHAA\Archiveros\Components\ArchiveroComponent())->generarPdf();
    });

    Route::get('/reporteExcelArchivero', function () {
        return (new \IEHAA\Archiveros\Components\ArchiveroComponent())->generarExcel();
    });
});
