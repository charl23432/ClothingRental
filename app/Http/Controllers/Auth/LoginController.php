<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $redirect = Auth::user()->role === 'admin'
                ? '/admin/dashboard'
                : '/men';

            return response()->json([
                'message' => 'Login successful.',
                'user' => Auth::user(),
                'redirect' => $redirect,
            ], 200);
        }

        return response()->json([
            'message' => 'The credentials do not match our records.',
            'errors' => [
                'email' => ['The credentials do not match our records.']
            ]
        ], 422);
    }

    public function logout(Request $request)
{
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'message' => 'Logged out successfully.',
        'redirect' => '/login',
    ], 200);
}
}