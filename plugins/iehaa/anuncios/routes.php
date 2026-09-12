<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFAnuncios', function () {
        return (new \Iehaa\Anuncios\Components\AnuncioComponent())->generarPdf();
    });

    Route::get('/reporteExcelAnuncios', function () {
        return (new \Iehaa\Anuncios\Components\AnuncioComponent())->generarExcel();
    });
});
