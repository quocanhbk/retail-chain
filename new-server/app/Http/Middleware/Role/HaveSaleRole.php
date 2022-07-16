<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HaveSaleRole
{
    public function handle(Request $request, Closure $next)
    {
        $have_sale_role = Auth::user()
            ->employment->roles->where("role", "sale")
            ->first();
        if (!$have_sale_role) {
            return response()->json(
                [
                    "message" => "Unauthorized.",
                ],
                403
            );
        }

        return $next($request);
    }
}
