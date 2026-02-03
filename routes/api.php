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

Route::patch('lists/{list}/items/{item}/toggle', [App\Http\Controllers\ListItemController::class, 'toggle']);

Route::post('lists/{list}/invitations', [App\Http\Controllers\ListInvitationsController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('lists.invitations.store');

Route::get('lists/{list}/invitations/{invitation:token}', [App\Http\Controllers\ListInvitationsController::class, 'show'])->name('lists.invitations.show');

Route::post('lists/{list}/invitations/{invitation:token}/accept', [App\Http\Controllers\ListInvitationsController::class, 'accept'])
    ->middleware('auth:sanctum')
    ->name('lists.invitations.accept');
