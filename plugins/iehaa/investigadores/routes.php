<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFInvestigador', function () {
        return (new \IEHAA\Investigadores\Components\InvestigadorComponent())->generarPdf();
    });

    Route::get('/reporteExcelInvestigador', function () {
        return (new \IEHAA\Investigadores\Components\InvestigadorComponent())->generarExcel();
    });
});
