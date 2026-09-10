<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFProyectos', function () {
        return (new \Iehaa\Proyectos\Components\ProyectoComponent())->generarPdf();
    });

    Route::get('/reporteExcelProyectos', function () {
        return (new \Iehaa\Proyectos\Components\ProyectoComponent())->generarExcel();
    });
});
