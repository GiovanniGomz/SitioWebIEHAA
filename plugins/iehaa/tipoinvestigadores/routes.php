<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFTipoInvestigadores', function () {
        return (new \IEHAA\Tipoinvestigadores\Components\TipoInvestigadorComponent())->generarPdf();
    });

    Route::get('/reporteExcelTipoInvestigadores', function () {
        return (new \IEHAA\Tipoinvestigadores\Components\TipoInvestigadorComponent())->generarExcel();
    });
});
