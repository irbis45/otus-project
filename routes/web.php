<?php

use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ProfileController;
use App\Http\Controllers\Public\CommentController;
use Illuminate\Support\Facades\Auth;

// Авторизация и профиль
Auth::routes();

// Публичная часть
Route::get('/', HomeController::class)->name('home');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/categories', CategoryController::class)->name('categories.index');
Route::get('/category/{slug}', [NewsController::class, 'byCategory'])->name('news.category');
Route::get('/search', [NewsController::class, 'search'])->name('news.search');

Route::middleware('auth')->group(function () {
    // Профиль
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Комментарии
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
});
