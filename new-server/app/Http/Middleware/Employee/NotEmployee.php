<?php

namespace App\Http\Middleware\Employee;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotEmployee
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('employees')->check()) {
            return response()->json([
                'message' => 'You are already logged in.',
            ], 400);
        }
        return $next($request);
    }
}
