<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::resource('lists.invitations', App\Http\Controllers\V1\ListInvitationsController::class)
        ->scoped(['invitation' => 'token']);
    Route::post('lists/{list}/invitations/{invitation:token}/accept', [App\Http\Controllers\V1\ListInvitationsController::class, 'accept'])
        ->middleware(['auth:sanctum', 'throttle:accept_invite'])
        ->name('lists.invitations.accept');
    Route::resource('lists.items', App\Http\Controllers\V1\ListItemController::class)
        ->scoped(['item' => 'uuid'])
        ->only(['store', 'update', 'destroy'])
        ->middleware(['auth:sanctum', 'throttle:api']);
    Route::post('lists/{list}/items/bulk', [App\Http\Controllers\V1\ListItemController::class, 'bulkStore'])
        ->middleware(['auth:sanctum', 'throttle:api'])
        ->name('lists.items.bulkStore');

    Route::patch('lists/{list}/items/{item}/toggle', [App\Http\Controllers\V1\ListItemController::class, 'toggle'])
        ->middleware(['auth:sanctum', 'throttle:api'])
        ->name('lists.items.toggle');
});

Route::post('/identities', [App\Http\Controllers\IdentityController::class, '__invoke',])
    ->middleware('throttle:identities')
    ->name('identities.store');

Route::resource('lists', App\Http\Controllers\CustomListController::class)
    ->only(['index', 'store', 'update', 'destroy', 'show'])
    ->middleware(['auth:sanctum', 'throttle:api']);
