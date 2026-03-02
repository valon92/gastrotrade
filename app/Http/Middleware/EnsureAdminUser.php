<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUser
{
    /**
     * Ensure the authenticated user is an admin (User model), not a client token.
     * Protects admin routes from being accessed with client Sanctum tokens.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof User || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Vetëm administratorët mund të aksesojnë këtë faqe.',
            ], 403);
        }

        return $next($request);
    }
}
