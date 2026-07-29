<?php

use App\Http\Controllers\FriendController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('feed', [PostController::class, 'index'])->name('feed');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');

    // PostPolicy runs as route middleware rather than inside the controller,
    // so ownership is refused before the Form Request validates: a stranger
    // editing someone else's post is told no, not told their text is invalid.
    Route::get('posts/{post}/edit', [PostController::class, 'edit'])
        ->can('update', 'post')
        ->name('posts.edit');

    Route::patch('posts/{post}', [PostController::class, 'update'])
        ->can('update', 'post')
        ->name('posts.update');

    Route::delete('posts/{post}', [PostController::class, 'destroy'])
        ->can('delete', 'post')
        ->name('posts.destroy');

    // Three routes, one page component with a variant, so each section of the
    // friends destination is directly linkable and independently testable.
    Route::get('friends', [FriendController::class, 'index'])->name('friends.index');
    Route::get('friends/requests', [FriendController::class, 'requests'])->name('friends.requests');
    Route::get('friends/find', [FriendController::class, 'find'])->name('friends.find');

    Route::post('friendships', [FriendshipController::class, 'store'])->name('friendships.store');
});

require __DIR__.'/settings.php';
