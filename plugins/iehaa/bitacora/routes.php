<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'cpanel.auth'])->group(function () {
    Route::get('/reportePDFBitacora', function () {
        if (!\Iehaa\Usuarios\Classes\CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }
        return (new \Iehaa\Bitacora\Components\BitacoraComponent())->generarPdf();
    });

    Route::get('/reporteExcelBitacora', function () {
        if (!\Iehaa\Usuarios\Classes\CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }
        return (new \Iehaa\Bitacora\Components\BitacoraComponent())->generarExcel();
    });
});
