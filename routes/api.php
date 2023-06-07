<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AuthController;
use App\Http\Controllers\Cms\UserController;
use App\Http\Controllers\Cms\GameController;
use App\Http\Controllers\Cms\ImageController;

/** Routes for CMS login */
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('register', 'register')->name('register');
    Route::post('logout', 'logout')->name("logout");
    Route::post('refresh', 'refresh')->name("refresh");
})->middleware(['api', 'cors']);

/** Routes for CMS actions */
Route::prefix('cms')->middleware(['api', 'cors'])->group(function() {
    Route::resource('users', UserController::class)->except(['create', 'edit' ]);
    Route::get('users/unique/{key}/{value?}', [UserController::class, 'unique'])->name('users.unique');
    Route::resource('games', GameController::class)->except(['create', 'edit']);
    Route::resource('images', ImageController::class)->except(['create', 'edit']);
});

/**
 * kregames.hu/api/cms/
 * kregames.hu/api/public/hu/
 */

