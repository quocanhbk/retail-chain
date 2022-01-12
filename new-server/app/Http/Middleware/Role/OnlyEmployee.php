<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnlyEmployee
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('employees')->check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }
        return $next($request);
    }
}
