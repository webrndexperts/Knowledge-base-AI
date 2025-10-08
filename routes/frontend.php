<?php

use App\Livewire\Admin\PdfUploader;
use App\Livewire\Admin\Articles\Index as ArticleView;
use App\Livewire\Admin\Users\Index as UserView;
use App\Livewire\Backend\ConversationManagement;
use App\Livewire\Backend\ConversationDetail;
use App\Livewire\Admin\Users\Create as UserCreate;
use App\Livewire\Admin\Users\Edit as UserEdit;
use App\Livewire\Admin\Users\Show as UserShow;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Articles Management
    Route::prefix('articles')->name('articles.')->group(function () {
        Route::get('list', ArticleView::class)->name('list');
        Route::get('upload/pdf', PdfUploader::class)->name('upload.pdf');
    });

    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', UserView::class)->name('index');
        Route::get('/create', UserCreate::class)->name('create');
        Route::get('/{user}', UserShow::class)->name('show');
        Route::get('/{user}/edit', UserEdit::class)->name('edit');
    });

    // Conversations Management
    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/{userId}', ConversationManagement::class)->name('index');
        Route::get('/{encryptedId}', ConversationDetail::class)->name('show');
    });
});
