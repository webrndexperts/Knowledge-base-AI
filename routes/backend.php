<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['role:admin'])->group(function () {
    Route::name('roles.')->group(function () {
        // require __DIR__.'/roles.php';
    });
});
