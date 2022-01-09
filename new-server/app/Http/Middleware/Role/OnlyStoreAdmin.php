<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnlyStoreAdmin
{
    public function handle(Request $request, Closure $next)
    {
        error_log("OnlyStoreAdmin");
        if (!Auth::guard('stores')->check()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }
        return $next($request);
    }
}
