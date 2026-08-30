<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFCategoriaInvestigadores', function () {
    return (new \IEHAA\Categoriainvestigadores\Components\CategoriaInvestigadorComponent())->generarPdf();
});

Route::get('/reporteExcelCategoriaInvestigadores', function () {
    return (new \IEHAA\Categoriainvestigadores\Components\CategoriaInvestigadorComponent())->generarExcel();
});
