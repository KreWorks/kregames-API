<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cms\AuthController;
use App\Http\Controllers\Cms\UserController;
use App\Http\Controllers\Cms\GameController;

/** Routes for CMS login */
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('register', 'register')->name('register');
    Route::post('logout', 'logout')->name("logout");
    Route::post('refresh', 'refresh')->name("refresh");
})->middleware('api');

/** Routes for CMS actions */
Route::prefix('cms')->middleware('api')->group(function() {
    Route::resource('users', UserController::class)->except(['edit']);
    Route::resource('games', GameController::class)->except(['edit']);
});

/**
 * kregames.hu/api/cms/
 * kregames.hu/api/public/hu/
 */

