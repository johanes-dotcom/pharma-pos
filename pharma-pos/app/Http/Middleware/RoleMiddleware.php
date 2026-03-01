<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Check if user has role loaded
        if (!$user->role) {
            Auth::logout();
            return redirect('/login')->with('error', 'Role tidak ditemukan');
        }

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect('/login')->with('error', 'Akun Anda tidak aktif');
        }

        // Check if user has the required role
        if ($user->role->nama_role !== $role) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Anda tidak memiliki akses ke halaman ini'
                ], 403);
            }
            return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        return $next($request);
    }
}
