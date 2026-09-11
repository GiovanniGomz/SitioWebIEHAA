<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFActivoFijo', function () {
        return (new \Iehaa\Inventario\Components\ActivoFijoComponent())->generarPdf();
    });

    Route::get('/reporteExcelActivoFijo', function () {
        return (new \Iehaa\Inventario\Components\ActivoFijoComponent())->generarExcel();
    });

    Route::get('/descarga-activo-fijo/{archivoId}', function ($archivoId) {
        return (new \Iehaa\Inventario\Components\ActivoFijoComponent())->descargar($archivoId);
    })->where('archivoId', '[0-9]+');
});
