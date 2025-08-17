<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::prefix('v1') ->as('v1.')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    //Route::post('/register', [AuthController::class, 'register']); // Не используется, так как регистрация пользователя через API не предусмотрена.
});

Route::prefix('v1')
     ->as('v1.')
     ->middleware('auth:api')
     ->group(function () {
         Route::post('/logout', [AuthController::class, 'logout']);

         Route::apiResource('news', NewsController::class);
     });
