<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records "last seen" for chat presence, at most once a minute per user.
 * Uses the query builder so it never touches the activity log.
 */
class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && (! $user->last_seen_at || $user->last_seen_at->lt(now()->subMinute()))) {
            User::whereKey($user->id)->update(['last_seen_at' => now()]);
        }

        return $next($request);
    }
}
