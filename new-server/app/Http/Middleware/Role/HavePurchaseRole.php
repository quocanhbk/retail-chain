<?php

namespace App\Http\Middleware\Role;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HavePurchaseRole
{
    public function handle(Request $request, Closure $next)
    {
        $have_purchase_role = Auth::user()->employment->roles->where('role', 'purchase')->first();
        if (!$have_purchase_role) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }
        return $next($request);
    }
}
