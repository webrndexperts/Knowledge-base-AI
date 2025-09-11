<?php

use App\Livewire\Admin\PdfUploader;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('upload/pdf', PdfUploader::class)->name('upload.pdf');
});
