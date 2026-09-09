<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFFacultades', function () {
        return (new \IEHAA\Facultades\Components\FacultadComponent())->generarPdf();
    });

    Route::get('/reporteExcelFacultades', function () {
        return (new \IEHAA\Facultades\Components\FacultadComponent())->generarExcel();
    });
});
