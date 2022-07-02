<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotStoreAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard("stores")->check()) {
            return response()->json(["message" => "You are already logged in."], 400);
        }
        return $next($request);
    }
}
