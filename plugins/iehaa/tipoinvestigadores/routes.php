<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFTipoInvestigadores', function () {
    return (new \IEHAA\Tipoinvestigadores\Components\TipoInvestigadorComponent())->generarPdf();
});

Route::get('/reporteExcelTipoInvestigadores', function () {
    return (new \IEHAA\Tipoinvestigadores\Components\TipoInvestigadorComponent())->generarExcel();
});
