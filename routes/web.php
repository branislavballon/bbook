<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('feed', [PostController::class, 'index'])->name('feed');
    Route::post('posts', [PostController::class, 'store'])->name('posts.store');
    // A post nobody linked to is still guessable by id, so the detail route
    // is gated by PostPolicy::view — which asks Post::scopeVisibleTo — rather
    // than relying on the feed to be the only way in.
    Route::get('posts/{post}', [PostController::class, 'show'])
        ->can('view', 'post')
        ->name('posts.show');

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

    // Both halves of liking authorize against the parent post's view ability,
    // not against a like of their own: a post someone cannot read is a post
    // they cannot act on.
    Route::post('posts/{post}/likes', [LikeController::class, 'store'])
        ->can('view', 'post')
        ->name('posts.likes.store');

    Route::delete('posts/{post}/likes', [LikeController::class, 'destroy'])
        ->can('view', 'post')
        ->name('posts.likes.destroy');

    // Commenting authorizes against the parent post's view ability for the
    // same reason liking does: the visibility rule has to hold for writes as
    // well as reads. There is no index route — the detail page carries the
    // thread — and no update or destroy: comments are append-only.
    Route::post('posts/{post}/comments', [CommentController::class, 'store'])
        ->can('view', 'post')
        ->name('posts.comments.store');

    // Three routes, one page component with a variant, so each section of the
    // friends destination is directly linkable and independently testable.
    Route::get('friends', [FriendController::class, 'index'])->name('friends.index');
    Route::get('friends/requests', [FriendController::class, 'requests'])->name('friends.requests');
    Route::get('friends/find', [FriendController::class, 'find'])->name('friends.find');

    // Public profiles are `users.show` rather than `profile.show`: the
    // settings screens already own the `profile.*` names, and this route is
    // about anyone on the network, not about the person editing their own
    // account. A person's own profile is this route pointed at themselves.
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

    Route::post('friendships', [FriendshipController::class, 'store'])->name('friendships.store');

    // FriendshipPolicy::respond gates both answers, as route middleware for
    // the same reason PostPolicy is: only the addressee decides, and that is
    // refused before the controller does anything.
    Route::patch('friendships/{friendship}', [FriendshipController::class, 'update'])
        ->can('respond', 'friendship')
        ->name('friendships.update');

    Route::delete('friendships/{friendship}', [FriendshipController::class, 'destroy'])
        ->can('respond', 'friendship')
        ->name('friendships.destroy');
});

require __DIR__.'/settings.php';
