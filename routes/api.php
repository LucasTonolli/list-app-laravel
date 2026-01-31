<?php

use App\Http\Middleware\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/identities', [App\Http\Controllers\IdentityController::class, '__invoke']);

Route::resource('lists', App\Http\Controllers\ListController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware(UserToken::class);
