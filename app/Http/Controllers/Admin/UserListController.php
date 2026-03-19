<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserListController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')
            ->orderByDesc('last_seen')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'number' => $user->number ?? '—',
                    'address' => $user->address ?? '—',
                    'photo_url' => $user->profile_photo
                        ? asset('storage/' . $user->profile_photo)
                        : asset('images/user-icon.png'),
                    'is_online' => $user->isOnline(),
                    'last_seen_human' => $user->last_seen
                        ? $user->lastSeenForHumans()
                        : null,
                ];
            });

        return response()->json($users);
    }

    public function view(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'number' => $user->number ?? '—',
            'address' => $user->address ?? '—',
            'photo_url' => $user->profile_photo
                ? asset('storage/' . $user->profile_photo)
                : asset('images/user-icon.png'),
            'is_online' => $user->isOnline(),
            'last_seen_human' => $user->last_seen
                ? $user->lastSeenForHumans()
                : null,
        ]);
    }
}