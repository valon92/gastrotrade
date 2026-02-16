<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $requiredRole = 'admin'): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Ju nuk keni akses në këtë faqe'
            ], 403);
        }

        // If required role is 'admin', check if user is full admin
        if ($requiredRole === 'admin' && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Ju nuk keni të drejtë për këtë veprim. Kërkohet rol admin.'
            ], 403);
        }

        return $next($request);
    }
}

