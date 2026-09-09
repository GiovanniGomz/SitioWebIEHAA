<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFEstante', function () {
        return (new \IEHAA\Estante\Components\EstanteComponent())->generarPdf();
    });

    Route::get('/reporteExcelEstante', function () {
        return (new \IEHAA\Estante\Components\EstanteComponent())->generarExcel();
    });
});
