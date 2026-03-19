<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\CheckoutController;

// Auth
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/logout', [LoginController::class, 'logout']);

// Current logged-in user
Route::middleware('auth')->get('/me', function (Request $request) {
    return response()->json($request->user());
});


Route::middleware('auth')->group(function () {
    Route::post('/api/checkout/store', [CheckoutController::class, 'store']);
});

// Profile/session routes
Route::middleware('auth')->group(function () {
    Route::get('/api/profile', [ProfileController::class, 'profile']);
    Route::post('/api/profile/update', [ProfileController::class, 'update']);
    Route::post('/api/reservations/{reservation}/update', [ProfileController::class, 'updateReservation']);
    Route::post('/api/profile/reset-password', [ProfileController::class, 'resetPassword']);
});

// PDF
Route::get('/reports/pdf', [ReportController::class, 'reportPdf']);

// Vue catch-all LAST
Route::get('/{vue_capture?}', function () {
    return view('welcome');
})->where('vue_capture', '[\/\w\.-]*');