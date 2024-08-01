<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        // Check if the user is authenticated and has the required role
        if (!Auth::check() || !(Auth::user()->user_role_id === 2 || Auth::user()->user_role_id === 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                // 'role' => Auth::user() ? Auth::user()->user_role_id : 'Not Authenticated'
            ], 403);
        }

        return $next($request);
    }
}
