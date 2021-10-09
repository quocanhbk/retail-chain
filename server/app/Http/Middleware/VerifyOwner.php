<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class VerifyOwner
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
        $user = auth()->user();
        $store_id = $request->route()->parameter('store_id');
        $is_owner = DB::table('stores')
                    ->where('owner_id', $user->id)
                    ->where('id', $store_id)
                    ->exists();
        if($is_owner){
            return $next($request);
        } else {
            $state = "fail";
            $errors = "this API can only be performed by owner of the store";
            return response()->json(compact('state','errors'));
        }
    }
}
