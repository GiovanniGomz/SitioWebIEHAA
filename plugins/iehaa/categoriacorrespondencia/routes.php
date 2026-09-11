<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFCategoriasCorrespondencia', function () {
        return (new \Iehaa\Categoriacorrespondencia\Components\CategoriaCorrespondenciaComponent())->generarPdf();
    });

    Route::get('/reporteExcelCategoriasCorrespondencia', function () {
        return (new \Iehaa\Categoriacorrespondencia\Components\CategoriaCorrespondenciaComponent())->generarExcel();
    });
});
