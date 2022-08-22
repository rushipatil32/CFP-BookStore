<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api'], function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('forgotPassword', [UserController::class, 'forgotPassword']);
    Route::post('resetPassword', [UserController::class, 'resetPassword']);

    Route::post('addBook', [BookController::class, 'addBook']);
    Route::post('updateBookByBookId', [BookController::class, 'updateBookByBookId']);
    Route::post('deleteBookByBookId', [BookController::class, 'deleteBookByBookId']);
    Route::get('getAllBooks', [BookController::class, 'getAllBooks']);


    Route::post('addBookToCartByBookId', [CartController::class, 'addBookToCartByBookId']);
    Route::post('updateBookQuantityInCart', [CartController::class, 'updateBookQuantityInCart']);
    Route::post('deleteBookByCartId', [CartController::class, 'deleteBookByCartId']);
    Route::get('getAllBooksByUserId', [CartController::class, 'getAllBooksByUserId']);

    Route::post('addAddress',[CartController::class, 'addAddress']);
    Route::post('placeOrder',[CartController::class, 'placeOrder']);

    

});
