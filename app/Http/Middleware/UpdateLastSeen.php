<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    public function handle($request, Closure $next)
    {
        // ✅ Only run if user is logged in
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Extra safety
            if ($user) {
                // Avoid excessive DB writes (update once per minute)
                if (!$user->last_seen || $user->last_seen->lt(now()->subMinute())) {
                    $user->last_seen = now();
                    $user->save();
                }
            }
        }

        return $next($request);
    }
}
