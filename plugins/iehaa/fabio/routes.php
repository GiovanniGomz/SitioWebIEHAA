<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFFabio', function () {
        return (new \IEHAA\Fabio\Components\FabioComponent())->generarPdf();
    });

    Route::get('/reporteExcelFabio', function () {
        return (new \IEHAA\Fabio\Components\FabioComponent())->generarExcel();
    });
});
