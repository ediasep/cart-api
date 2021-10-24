<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

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

// Cart
Route::get('carts', [CartController::class, 'show_user_cart']);
Route::post('carts/add', [CartController::class, 'add_to_cart']);
Route::put('carts/update', [CartController::class, 'update_cart']);
Route::delete('carts/delete', [CartController::class, 'remove_from_cart']);
Route::patch('carts/checkout', [CartController::class, 'checkout']);

// Product
Route::get('products', [ProductController::class, 'index']);
Route::get('products/show', [ProductController::class, 'show']);

// User
Route::get('users', [UserController::class, 'index']);
