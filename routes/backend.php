<?php

use App\Livewire\Admin\RoleCrud;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:admin'])->group(function () {
    // Roles Management
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', RoleCrud::class)->name('index');
    });
});
