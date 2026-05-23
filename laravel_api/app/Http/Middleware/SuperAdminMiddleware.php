<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Chỉ cho phép role 'admin' (Root Admin) truy cập
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Super Admin access required'], 403);
        }

        return $next($request);
    }
}
