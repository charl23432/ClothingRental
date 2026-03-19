<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * GET profile data for Vue
     */
    public function profile()
{
    /** @var User|null $user */
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    // ✅ FIX: transform notification data
    $notifications = $user->notifications()
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notification',
                'message' => $notification->data['message'] ?? '',
                'image' => $notification->data['image'] ?? null,
                'status' => $notification->data['status'] ?? null,
                'reservation_id' => $notification->data['reservation_id'] ?? null,
                'created_at' => $notification->created_at,
            ];
        });

    $reservations = Reservation::with('product')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'user' => $user,
        'reservations' => $reservations,
        'notifications' => $notifications,
    ]);
}
    /**
     * Update profile
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255|unique:users,email,' . $user->id,
            'number'  => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'avatar'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('profiles', 'public');
            $user->profile_photo = $path;
        }

        $user->name    = $request->name;
        $user->email   = $request->email;
        $user->number  = $request->number;
        $user->address = $request->address;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Update reservation details
     */
    public function updateReservation(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'size' => 'required|string',
                'delivery' => 'required|in:delivery,pickup',
                'rent_time' => 'required|date',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }

        $reservation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Reservation updated successfully.',
            'reservation' => $reservation
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'current_password' => ['Current password is incorrect.']
                ]
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }
}