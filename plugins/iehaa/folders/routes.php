<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFFolder', function () {
        return (new \IEHAA\Folders\Components\FolderComponent())->generarPdf();
    });

    Route::get('/reporteExcelFolder', function () {
        return (new \IEHAA\Folders\Components\FolderComponent())->generarExcel();
    });
});
