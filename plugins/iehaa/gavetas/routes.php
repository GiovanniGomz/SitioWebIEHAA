<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFGaveta', function () {
        return (new \IEHAA\Gavetas\Components\GavetaComponent())->generarPdf();
    });

    Route::get('/reporteExcelGaveta', function () {
        return (new \IEHAA\Gavetas\Components\GavetaComponent())->generarExcel();
    });
});
