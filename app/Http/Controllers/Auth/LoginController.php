<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The credentials do not match our records.',
                'errors' => [
                    'email' => ['The credentials do not match our records.']
                ]
            ], 422);
        }


        Auth::login($user);
        $request->session()->regenerate();

        $user->last_seen = now();
        $user->save();

        $redirect = $user->role === 'admin'
            ? '/admin/dashboard'
            : '/men';

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'redirect' => $redirect,
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $user->last_seen = now();
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
            'redirect' => '/login',
        ], 200);
    }
}