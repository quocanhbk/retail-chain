<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class VerifyBranch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $store_id = $request->route()->parameter('store_id');
        $branch_id = $request->route()->parameter('branch_id');
        $is_branchID_confirm = DB::table('branches')
                                    ->where('store_id', $store_id)
                                    ->where('id', $branch_id)
                                    ->exists();
        if($is_branchID_confirm){
            return $next($request);
        } else {
            $state = "fail";
            $errors = "This branch_id: $branch_id does not belong to store_id: $store_id";
            return response()->json(compact('state','errors'));
        }
    }
}
