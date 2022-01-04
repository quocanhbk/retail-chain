<?php

namespace App\Http\Middleware\Employee;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnlyEmployee
{
    public function handle(Request $request, Closure $next)
    {
        error_log("OnlyEmployee");
        if (!Auth::guard('employees')->check()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }
        return $next($request);
    }
}
