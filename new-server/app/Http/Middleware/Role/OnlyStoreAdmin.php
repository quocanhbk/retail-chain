<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnlyStoreAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard("stores")->check()) {
            return response()->json(
                [
                    "message" => "Unauthenticated.",
                ],
                401
            );
        }
        return $next($request);
    }
}
