<?php

use Illuminate\Support\Facades\Route;
use Tu\Plugin\Components\TuComponente;

Route::get('/reportePDFInvestigador', function () {
    return (new \IEHAA\Investigadores\Components\InvestigadorComponent())->generarPdf();
});

Route::get('/reporteExcelInvestigador', function () {
    return (new \IEHAA\Investigadores\Components\InvestigadorComponent())->generarExcel();
});
