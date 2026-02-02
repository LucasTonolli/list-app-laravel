<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/identities', [App\Http\Controllers\IdentityController::class, '__invoke']);

Route::resource('lists', App\Http\Controllers\CustomListController::class)
    ->only(['index', 'store', 'update', 'destroy', 'show'])
    ->middleware('auth:sanctum');

Route::resource('lists/{list}/items', App\Http\Controllers\ListItemController::class)
    ->only(['store', 'update', 'destroy'])
    ->middleware('auth:sanctum');
