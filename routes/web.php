<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // ✅ ADD THIS
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\CheckoutController;

/*
|--------------------------------------------------------------------------
| DEBUG MAIL CHECK (TEMPORARY)
|--------------------------------------------------------------------------
*/

Route::get('/mail-check', function () {
    return response()->json([
        'mailer' => config('mail.default'),
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'username' => config('mail.mailers.smtp.username'),
        'from' => config('mail.from.address'),
    ]);
});

/*
|--------------------------------------------------------------------------
| TEST MAIL (VERY IMPORTANT)
|--------------------------------------------------------------------------
*/

Route::get('/test-mail', function () {
    try {
        Mail::raw('Test email from Laravel.', function ($message) {
            $message->to('charlenebahandianon@gmail.com')
                    ->subject('Laravel Test Email');
        });

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully.',
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'from' => config('mail.from.address'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'from' => config('mail.from.address'),
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    */

    Route::get('/email/verify', function () {
        return response()->json([
            'message' => 'Please verify your email address.',
        ]);
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        $user = $request->user();

        if ($user && $user->role === 'admin') {
            return redirect('/admin/dashboard?verified=1');
        }

        return redirect('/men?verified=1');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $user = $request->user();

        if ($user && $user->role === 'admin') {
            return response()->json([
                'message' => 'Admin account does not require email verification.',
            ], 200);
        }

        if ($user && $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified.',
            ], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent successfully.',
        ], 200);
    })->middleware('throttle:6,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', function (Request $request) {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        return view('admin.dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| Verified User Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/checkout/store', [CheckoutController::class, 'store']);

    Route::get('/api/profile', [ProfileController::class, 'profile']);
    Route::post('/api/profile/notifications/read', [ProfileController::class, 'markNotificationsAsRead']);
    Route::post('/api/profile/update', [ProfileController::class, 'update']);
    Route::post('/api/reservations/{reservation}/update', [ProfileController::class, 'updateReservation']);
    Route::post('/api/profile/reset-password', [ProfileController::class, 'resetPassword']);

    Route::get('/men', function () {
        return view('welcome');
    });
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/reports/pdf', [ReportController::class, 'reportPdf']);

/*
|--------------------------------------------------------------------------
| Vue Catch-All (MUST BE LAST)
|--------------------------------------------------------------------------
*/

Route::get('/{vue_capture?}', function () {
    return view('welcome');
})->where('vue_capture', '[\/\w\.-]*');