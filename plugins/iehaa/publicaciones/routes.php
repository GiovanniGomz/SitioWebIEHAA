<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFPublicaciones', function () {
        return (new \Iehaa\Publicaciones\Components\PublicacionComponent())->generarPdf();
    });

    Route::get('/reporteExcelPublicaciones', function () {
        return (new \Iehaa\Publicaciones\Components\PublicacionComponent())->generarExcel();
    });
});
