<?php

namespace App\Http\Middleware;

use App\Models\PermissionRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HavePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $action)
    {
        if (Auth::guard("employees")->check()) {
            $employment_roles = Auth::user()
                ->employment->roles->pluck("role_id")
                ->toArray();
            $allowed_roles = PermissionRole::with("permission")
                ->where("store_id", 1)
                ->whereHas("permission", function ($query) use ($action) {
                    $query->where("action_slug", $action);
                })
                ->get()
                ->pluck("role_id")
                ->toArray();

            if (!array_intersect($employment_roles, $allowed_roles)) {
                return response()->json(["message" => "You are not allowed to perform this action"], 403);
            }
        }

        return $next($request);
    }
}
