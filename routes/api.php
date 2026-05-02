<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
| Mobile Auth
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Admin API
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [AdminController::class, 'index']);

// Inventory — kept original URL format
Route::get('/inventory-men', [InventoryController::class, 'men']);
Route::get('/inventory-women', [InventoryController::class, 'women']);
Route::get('/inventory-men-ps', [InventoryController::class, 'menPS']);
Route::get('/inventory-women-ps', [InventoryController::class, 'womenPS']);
Route::post('/inventory', [InventoryController::class, 'store']);
Route::get('/inventory/{item}', [InventoryController::class, 'show']);
Route::put('/inventory/{item}', [InventoryController::class, 'update']);
Route::delete('/inventory/{item}', [InventoryController::class, 'destroy']);

// Reservations
Route::get('/reservations/pending', [ReservationController::class, 'pending']);
Route::get('/reservations/confirmed', [ReservationController::class, 'confirmed']);
Route::post('/reservations/{reservation}/confirm', [ReservationController::class, 'confirm']);
Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
Route::post('/reservations/{reservation}/update', [ReservationController::class, 'update']);
Route::post('/reservations/{reservation}/return', [ReservationController::class, 'returnItem']);

// Users
Route::get('/admin/users', [UserListController::class, 'index']);
Route::get('/admin/users/{user}', [UserListController::class, 'view']);

// Settings
Route::get('/settings', [SettingController::class, 'index']);
Route::post('/settings', [SettingController::class, 'update']);

// Reports
Route::get('/reports', [ReportController::class, 'index']);

/*
|--------------------------------------------------------------------------
| User API
|--------------------------------------------------------------------------
*/
Route::get('/products/{id}', [ProductController::class, 'details']);
Route::get('/men', [ProductController::class, 'men']);
Route::get('/men/tuxedo', [ProductController::class, 'menTuxedo']);
Route::get('/men/prom', [ProductController::class, 'menProm']);
Route::get('/women', [ProductController::class, 'women']);
Route::get('/women/wedding', [ProductController::class, 'womenWedding']);
Route::get('/women/prom', [ProductController::class, 'womenProm']);

// Checkout
Route::get('/checkout/{id}', [CheckoutController::class, 'show']);