<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HaveManageRole
{
    public function handle(Request $request, Closure $next)
    {
        $have_manage_role = Auth::user()
            ->employment->roles->where("role", "manage")
            ->first();
        if (!$have_manage_role) {
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
