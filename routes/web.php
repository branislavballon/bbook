<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('feed', [PostController::class, 'index'])->name('feed');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
});

require __DIR__.'/settings.php';
