<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrSale
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard("stores")->check()) {
            $store_id = Auth::guard("stores")->user()->id;
            $request->attributes->add([
                "store_id" => $store_id,
            ]);
            return $next($request);
        }
        if (Auth::guard("employees")->check()) {
            $have_purchase_role = Auth::user()
                ->employment->roles->where("role", "sale")
                ->first();
            if (!$have_purchase_role) {
                return response()->json(
                    [
                        "message" => "Unauthorized.",
                    ],
                    403
                );
            }
            $store_id = Auth::user()->store_id;
            $request->attributes->add([
                "store_id" => $store_id,
            ]);
            return $next($request);
        }
        return response()->json(
            [
                "message" => "Unauthenticated.",
            ],
            401
        );
    }
}
