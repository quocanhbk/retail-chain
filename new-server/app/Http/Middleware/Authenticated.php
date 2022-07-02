<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(["message" => "Unauthenticated."], 401);
        }

        if (Auth::guard("employees")->check()) {
            if (Auth::user()->employment->to != null) {
                return response()->json(["message" => "Your employment is terminated."], 401);
            }
            $request->attributes->add([
                "as" => "employee",
                "store_id" => Auth::user()->store_id,
            ]);
        } else {
            $request->attributes->add([
                "as" => "admin",
                "store_id" => Auth::guard("stores")->user()->id,
            ]);
        }

        return $next($request);
    }
}
