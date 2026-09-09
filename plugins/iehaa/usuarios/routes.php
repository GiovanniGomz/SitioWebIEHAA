<?php

use Iehaa\Usuarios\Classes\CpanelAuth;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/logout', function () {
        CpanelAuth::logout();

        return redirect('/login');
    });
});
