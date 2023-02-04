<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;

//Original route version with this tutorial 
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('register', 'register')->name('register');
    Route::post('logout', 'logout')->name("logout");
    Route::post('refresh', 'refresh')->name("refresh");

})->middleware('api');

Route::controller(TodoController::class)->group(function () {
    Route::get('todos', 'index')->name("todos.index");
    Route::post('todo', 'store')->name("todos.store");
    Route::get('todo/{id}', 'show');
    Route::put('todo/{id}', 'update');
    Route::delete('todo/{id}', 'destroy');
})->middleware('api'); 
// I think better version 
Route::post('login', [AuthController::class, 'authenticate']);
//Route::post('register', [ApiController::class, 'register']);

/**
 * kregames.hu/api/cms/
 * kregames.hu/api/public/hu/
 */


Route::prefix('cms')->middleware('api')->group(function() {
    Route::get('logout', [AuthController::class, 'logout']);
    /*Route::get('get_user', [ApiController::class, 'get_user']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('create', [ProductController::class, 'store']);
    Route::put('update/{product}',  [ProductController::class, 'update']);
    Route::delete('delete/{product}',  [ProductController::class, 'destroy']);*/
});