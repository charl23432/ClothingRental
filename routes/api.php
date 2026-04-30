<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\UserListController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| AUTH (for mobile)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);

/*
|--------------------------------------------------------------------------
| PROTECTED USER
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Admin API (protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'index']);

    // Inventory
    Route::prefix('inventory')->group(function () {
        Route::get('/men', [InventoryController::class, 'men']);
        Route::get('/women', [InventoryController::class, 'women']);
        Route::get('/men-ps', [InventoryController::class, 'menPS']);
        Route::get('/women-ps', [InventoryController::class, 'womenPS']);
        Route::post('/', [InventoryController::class, 'store']);
        Route::get('/{item}', [InventoryController::class, 'show']);
        Route::put('/{item}', [InventoryController::class, 'update']);
        Route::delete('/{item}', [InventoryController::class, 'destroy']);
    });

    // Reservations
    Route::prefix('reservations')->group(function () {
        Route::get('/pending', [ReservationController::class, 'pending']);
        Route::get('/confirmed', [ReservationController::class, 'confirmed']);
        Route::post('/{reservation}/confirm', [ReservationController::class, 'confirm']);
        Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel']);
        Route::post('/{reservation}/update', [ReservationController::class, 'update']);
        Route::post('/{reservation}/return', [ReservationController::class, 'returnItem']);
    });

    // Users
    Route::prefix('admin/users')->group(function () {
        Route::get('/', [UserListController::class, 'index']);
        Route::get('/{user}', [UserListController::class, 'view']);
    });

    // Settings
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Public User API (no auth needed)
|--------------------------------------------------------------------------
*/

// Products
Route::prefix('products')->group(function () {
    Route::get('/{id}', [ProductController::class, 'details']);
});

Route::get('/men', [ProductController::class, 'men']);
Route::get('/men/tuxedo', [ProductController::class, 'menTuxedo']);
Route::get('/men/prom', [ProductController::class, 'menProm']);

Route::get('/women', [ProductController::class, 'women']);
Route::get('/women/wedding', [ProductController::class, 'womenWedding']);
Route::get('/women/prom', [ProductController::class, 'womenProm']);

// Checkout (FIXED duplicate)
Route::get('/checkout/{id}', [CheckoutController::class, 'show']);