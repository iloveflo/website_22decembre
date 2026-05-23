<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Cho phép cả nhân viên (staff) và quản trị viên (admin) truy cập
        if (!$user || ($user->role !== 'staff' && $user->role !== 'admin')) {
            return response()->json(['message' => 'Forbidden: Staff or Admin access required'], 403);
        }

        return $next($request);
    }
}
