<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrEmployee
{
    public function handle(Request $request, Closure $next)
    {
        $as = null;
        $store_id = null;

        if (Auth::guard("stores")->check()) {
            $store_id = Auth::guard("stores")->user()->id;
            $as = "admin";
        }

        if (Auth::guard("employees")->check()) {
            $store_id = Auth::user()->store_id;
            $as = "employee";
        }

        if ($as == null) {
            return response()->json(["message" => "Unauthenticated."], 401);
        }

        $request->attributes->add([
            "as" => $as,
            "store_id" => $store_id,
        ]);

        return $next($request);
    }
}
