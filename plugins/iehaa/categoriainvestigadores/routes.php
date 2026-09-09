<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFCategoriaInvestigadores', function () {
        return (new \IEHAA\Categoriainvestigadores\Components\CategoriaInvestigadorComponent())->generarPdf();
    });

    Route::get('/reporteExcelCategoriaInvestigadores', function () {
        return (new \IEHAA\Categoriainvestigadores\Components\CategoriaInvestigadorComponent())->generarExcel();
    });
});
